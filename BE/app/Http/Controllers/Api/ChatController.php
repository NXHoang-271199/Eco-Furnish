<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * ChatController - Xử lý các yêu cầu trò chuyện AI và tìm kiếm sản phẩm
 * 
 * Tính năng:
 * - Xử lý yêu cầu chat từ người dùng
 * - Phát hiện ý định tìm kiếm sản phẩm
 * - Trích xuất từ khóa tìm kiếm và không gian (space)
 * - Tìm kiếm sản phẩm theo từ khóa, giá, tên sản phẩm và không gian
 * - Phân loại sản phẩm theo danh mục
 * - Gọi API Gemini để tạo phản hồi thông minh
 * 
 * Cập nhật: Hỗ trợ tìm kiếm thông qua các không gian (spaces) như phòng khách,
 * phòng ngủ, phòng ăn, v.v. và lọc sản phẩm dựa trên các danh mục phù hợp với không gian đó.
 * Cập nhật 2: Thêm logic ưu tiên tìm kiếm theo tên sản phẩm.
 */
class ChatController extends Controller
{
    // Ánh xạ từ từ khóa tiếng Việt sang space_key trong cơ sở dữ liệu
    private $spaceKeyMapping = [
        'phòng khách' => 'living_room',
        'phòng ngủ' => 'bedroom',
        'phòng bếp' => 'kitchen',
        'phòng ăn' => 'dining_room',
        'văn phòng' => 'office',
        'ngoài trời' => 'outdoor',
        'phòng tắm' => 'bathroom',
        'khác' => 'other'
    ];
    
    // Từ vựng mở rộng liên quan đến không gian
    private $extendedSpaceVocab = [
        'living_room' => ['phòng khách', 'tiếp khách', 'phòng lớn', 'phòng chung', 'phòng tiếp khách'],
        'bedroom' => ['phòng ngủ', 'chỗ ngủ', 'phòng đi ngủ', 'phòng nghỉ ngơi'],
        'kitchen' => ['phòng bếp', 'bếp', 'nhà bếp', 'chỗ nấu ăn', 'khu vực nấu ăn'],
        'dining_room' => ['phòng ăn', 'chỗ ăn', 'khu vực ăn uống'],
        'office' => ['văn phòng', 'phòng làm việc', 'phòng học', 'phòng đọc sách', 'nơi làm việc'],
        'outdoor' => ['ngoài trời', 'sân vườn', 'ban công', 'sân thượng', 'hiên nhà'],
        'bathroom' => ['phòng tắm', 'nhà tắm', 'phòng vệ sinh', 'toilet', 'wc', 'nhà vệ sinh']
    ];
    
    /**
     * Xử lý yêu cầu chat và gửi đến API Gemini
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request)
    {
        // Validate request
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->input('message');

        try {
            // Kiểm tra xem người dùng có đang tìm kiếm sản phẩm không
            if ($this->detectProductSearchIntent($userMessage)) {
                // Trích xuất từ khóa tìm kiếm
                $searchInfo = $this->extractSearchKeywords($userMessage);
                $keywords = $searchInfo['keywords'];
                $priceInfo = $searchInfo['price_info'];
                $spaceKey = $searchInfo['space_key'] ?? null;
                
                // Thêm space_key vào thông tin giá để truyền đến phương thức searchProducts
                if ($spaceKey) {
                    $priceInfo['space_key'] = $spaceKey;
                }
                
                // Tìm kiếm sản phẩm
                $searchResults = $this->searchProducts($keywords, $priceInfo);
                $products = $searchResults['products'];
                $categories = $searchResults['categories'];
                $hasProductsInPriceRange = $searchResults['has_products_in_price_range'];
                
                // Log chi tiết về kết quả tìm kiếm
                Log::info("Kết quả tìm kiếm cho '{$keywords}':", [
                    'số_lượng_sản_phẩm' => count($products),
                    'có_sản_phẩm_trong_khoảng_giá' => $hasProductsInPriceRange ? 'Có' : 'Không',
                    'không_gian' => $spaceKey,
                    'giá_yêu_cầu' => $priceInfo['has_price'] ? json_encode($priceInfo) : 'Không có'
                ]);
                
                if (count($products) === 0 && $priceInfo && $priceInfo['has_price']) {
                    // Nếu không tìm thấy sản phẩm nào thỏa mãn điều kiện giá
                    // Thử tìm kiếm lại không có điều kiện giá để hiển thị một số kết quả gợi ý
                    $priceInfoWithoutPrice = $priceInfo;
                    $priceInfoWithoutPrice['has_price'] = false;
                    
                    $fallbackResults = $this->searchProducts($keywords, $priceInfoWithoutPrice);
                    if (count($fallbackResults['products']) > 0) {
                        Log::info("Tìm kiếm lại không có điều kiện giá: " . count($fallbackResults['products']) . " sản phẩm");
                        $products = $fallbackResults['products'];
                        $categories = $fallbackResults['categories'];
                        // Vẫn giữ hasProductsInPriceRange = false
                    }
                }
                
                // Gọi API Gemini để có phản hồi thông minh
                // Truyền thêm thông tin về kết quả tìm kiếm để Gemini có thể đưa ra phản hồi phù hợp
                $aiResponse = $this->callGeminiApi($userMessage, [
                    'is_product_search' => true,
                    'search_keywords' => $keywords,
                    'found_products' => count($products) > 0, 
                    'product_count' => count($products),
                    'categories_found' => $categories,
                    'price_info' => $priceInfo,
                    'has_products_in_price_range' => $hasProductsInPriceRange,
                    'space_key' => $spaceKey // Thêm space_key vào context
                ]);

                // In thông tin debug ra console
                Log::info('Kết quả API trả về', [
                    'products_count' => count($products),
                    'has_products' => count($products) > 0,
                    'first_product' => count($products) > 0 ? $products[0]['name'] : 'không có',
                    'price_info' => $priceInfo
                ]);

                // Trả về cả phản hồi AI và sản phẩm
                return response()->json([
                    'success' => true,
                    'reply' => $aiResponse,
                    'has_products' => count($products) > 0,
                    'products' => $products,
                    'categories' => $categories,
                    'search_keywords' => $keywords,
                    'price_info' => $priceInfo,
                    'has_products_in_price_range' => $hasProductsInPriceRange,
                    'space_key' => $spaceKey // Thêm space_key vào response
                ]);
            }

            // Nếu không phải tìm kiếm sản phẩm, chỉ gọi API Gemini
            $response = $this->callGeminiApi($userMessage);

            return response()->json([
                'success' => true,
                'reply' => $response,
                'has_products' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xử lý yêu cầu của bạn.',
                'error' => $e->getMessage(),
                'reply' => 'Xin lỗi, đã xảy ra lỗi khi xử lý tin nhắn của bạn. Vui lòng thử lại sau.',
                'has_products' => false
            ], 500);
        }
    }

    /**
     * Phát hiện ý định tìm kiếm sản phẩm từ tin nhắn
     *
     * @param  string  $message
     * @return bool
     */
    private function detectProductSearchIntent($message)
    {
        $message = mb_strtolower($message, 'UTF-8');
        
        // Kiểm tra nếu tin nhắn chỉ là lời chào đơn giản
        $greetings = [
            'chào', 'hello', 'hi', 'xin chào', 'chào bạn', 
            'chào trợ lý', 'hey', 'alo', 'hola'
        ];
        
        // Nếu tin nhắn chỉ chứa lời chào và ít hơn 15 ký tự, không coi là tìm kiếm sản phẩm
        if (mb_strlen($message) < 15) {
            foreach ($greetings as $greeting) {
                if (mb_strpos($message, $greeting) !== false) {
                    // Kiểm tra xem lời chào có chiếm hầu hết tin nhắn không
                    if (mb_strlen($greeting) > mb_strlen($message) * 0.5) {
                        return false;
                    }
                }
            }
        }
        
        // Các từ khóa liên quan đến tìm kiếm sản phẩm
        $searchKeywords = [
            'tìm', 'kiếm', 'mua', 'sản phẩm', 'đồ', 'nội thất',
            'giới thiệu', 'cho tôi xem', 'có bán', 'bán không',
            'bàn', 'ghế', 'tủ', 'giường', 'kệ', 'đèn', 'thảm',
            'gợi ý', 'cần', 'muốn', 'tư vấn', 'gương', 'sofa',
            'giá', 'mẫu', 'loại', 'hiện có', 'phòng', 'thiết kế'
        ];

        // Kiểm tra các không gian từ thuộc tính của lớp
        foreach ($this->spaceKeyMapping as $spaceText => $spaceKey) {
            if (mb_strpos($message, $spaceText) !== false) {
                return true;
            }
        }
        
        // Kiểm tra trong từ vựng mở rộng về không gian
        foreach ($this->extendedSpaceVocab as $spaceKey => $spaceTerms) {
            foreach ($spaceTerms as $term) {
                if (mb_strpos($message, $term) !== false) {
                    return true;
                }
            }
        }
        
        // Các cụm từ chỉ rõ ý định tìm kiếm sản phẩm
        $searchPhrases = [
            'có sản phẩm', 'tìm sản phẩm', 'mua sản phẩm', 

            'giới thiệu sản phẩm', 'tư vấn sản phẩm',
            'có bán', 'mua được', 'tìm mua', 'giới thiệu cho tôi',
            'cho tôi xem', 'cần mua', 'muốn mua', 'tìm kiếm',
            'có mẫu', 'có loại', 'có đồ', 'có nội thất',
            'trang trí', 'thiết kế', 'nội thất cho', 'đồ cho'
        ];

        // Kiểm tra các cụm từ trước
        foreach ($searchPhrases as $phrase) {
            if (mb_strpos($message, $phrase) !== false) {
                return true;
            }
        }

        // Kiểm tra xem tin nhắn có chứa từ khóa tìm kiếm không
        foreach ($searchKeywords as $keyword) {
            if (mb_strpos($message, $keyword) !== false) {
                return true;
            }
        }

        // Kiểm tra các mẫu câu hỏi thường gặp về sản phẩm
        $questionPatterns = [
            '/có.*(bàn|ghế|tủ|giường|kệ|đèn|thảm|gương|sofa).*không/i',
            '/(bàn|ghế|tủ|giường|kệ|đèn|thảm|gương|sofa).*giá/i',
            '/giá.*(bàn|ghế|tủ|giường|kệ|đèn|thảm|gương|sofa)/i',
            '/(bàn|ghế|tủ|giường|kệ|đèn|thảm|gương|sofa).*nào/i',
            '/nào.*(bàn|ghế|tủ|giường|kệ|đèn|thảm|gương|sofa)/i',
            '/phòng.*(khách|ngủ|làm việc|ăn)/i',
            '/(khách|ngủ|làm việc|ăn).*phòng/i',
        ];

        foreach ($questionPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Trích xuất từ khóa tìm kiếm từ tin nhắn
     *
     * @param  string  $message
     * @return array
     */
    private function extractSearchKeywords($message)
    {
        $message = mb_strtolower($message, 'UTF-8');

        // Loại bỏ các từ không cần thiết
        $stopWords = [
            'hãy', 'vui lòng', 'làm ơn', 'giúp', 'tôi', 'mình', 'bạn', 'cho', 'tìm', 'kiếm',
            'giới thiệu', 'sản phẩm', 'về', 'có', 'không', 'được', 'muốn', 'cần', 'xem',
            'một', 'cái', 'chiếc', 'đó', 'này', 'kia', 'thế', 'là', 'và', 'hay', 'hoặc',
            'của', 'với', 'từ', 'đến', 'ra', 'vào', 'lên', 'xuống', 'nào', 'đâu', 'ai',
            'thì', 'mà', 'để', 'còn', 'đang', 'sẽ', 'đã', 'rồi', 'nên', 'cần', 'phải',
            'như', 'trên', 'dưới', 'trong', 'ngoài', 'giữa', 'quanh', 'chung', 'riêng'
        ];

        // Các danh mục sản phẩm cần tìm
        $categories = [
            'bàn' => 1,
            'ghế' => 2,
            'sofa' => 3,
            'giường' => 4,
            'tủ' => 5,
            'đèn' => 6,
            'gương' => 7,
            'thảm' => 8,
            'kệ' => 9
        ];

        // Các từ mô tả không gian
        $spaces = array_keys($this->spaceKeyMapping);
        
        // Các từ mô tả phong cách
        $styles = ['hiện đại', 'cổ điển', 'tối giản', 'scandinavian', 'vintage', 'industrial', 'bohemian', 'rustic'];
        
        // Các từ mô tả chất liệu
        $materials = ['gỗ', 'kim loại', 'nhựa', 'tre', 'mây', 'vải', 'da', 'thủy tinh', 'đá'];
        
        // Các từ/ký hiệu liên quan đến giá cần loại bỏ sau khi trích xuất giá
        $priceRelatedWords = [
            'giá', 'tiền', 'vnd', 'đồng', 'đ', 'k', 'tr', 'triệu', 'nghìn', 'ngàn',
            'dưới', 'trên', 'khoảng', 'tầm', 'từ', 'đến', 'tới', '<=', '>=', '< ', '> ', '-'
        ];

        // Phát hiện giá từ tin nhắn
        $priceInfo = $this->extractPriceInfo($message);

        // Lưu lại message gốc để loại bỏ phần giá sau
        $messageWithoutPrice = $message;

        // Loại bỏ các phần liên quan đến giá đã được phát hiện khỏi messageWithoutPrice
        if ($priceInfo['has_price']) {
            // Tìm các số trong tin nhắn
            preg_match_all('/\d+[k\.]?\d*/', $message, $numbers);
            if (!empty($numbers[0])) {
                foreach ($numbers[0] as $number) {
                    // Chỉ thay thế số nếu nó đứng một mình hoặc kèm đơn vị tiền tệ
                    // Tránh thay thế số trong tên sản phẩm (VD: Ghế Eames G01)
                    $messageWithoutPrice = preg_replace('/\b' . preg_quote($number, '/') . '\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?\b/iu', '', $messageWithoutPrice);
                }
            }
            // Loại bỏ các từ liên quan đến giá
            foreach ($priceRelatedWords as $word) {
                // Dùng regex để thay thế chính xác hơn, tránh thay thế một phần của từ khác
                $messageWithoutPrice = preg_replace('/\b' . preg_quote($word, '/') . '\b/iu', '', $messageWithoutPrice);
            }
            // Loại bỏ khoảng trắng thừa
            $messageWithoutPrice = preg_replace('/\s+/', ' ', $messageWithoutPrice);
            $messageWithoutPrice = trim($messageWithoutPrice);
            Log::info("Tin nhắn sau khi loại bỏ phần giá: '{$messageWithoutPrice}'");
        }

        // Tìm tất cả danh mục sản phẩm trong tin nhắn ĐÃ LOẠI BỎ GIÁ
        $foundCategories = [];
        foreach ($categories as $category => $id) {
            if (mb_strpos($messageWithoutPrice, $category) !== false) {
                $foundCategories[] = $category;
            }
        }

        // Tìm không gian trong tin nhắn ĐÃ LOẠI BỎ GIÁ - phiên bản cải tiến
        $foundSpace = null;
        $foundSpaceKey = null;

        // Trước tiên tìm từ khớp chính xác với map gốc
        foreach ($this->spaceKeyMapping as $spaceText => $spaceKey) {
            if (mb_strpos($messageWithoutPrice, $spaceText) !== false) {
                $foundSpace = $spaceText;
                $foundSpaceKey = $spaceKey;
                break;
            }
        }

        // Nếu không tìm thấy, thử tìm trong từ vựng mở rộng
        if (!$foundSpace) {
            foreach ($this->extendedSpaceVocab as $spaceKey => $spaceTerms) {
                foreach ($spaceTerms as $term) {
                    if (mb_strpos($messageWithoutPrice, $term) !== false) {
                        // Lấy tên hiển thị từ ánh xạ gốc (đảo ngược)
                        $foundSpaceKey = $spaceKey;
                        $foundSpace = array_search($spaceKey, $this->spaceKeyMapping);
                        // Ưu tiên key gốc nếu tồn tại
                        if (!$foundSpace) $foundSpace = $term; 
                        break 2; // Thoát cả 2 vòng lặp
                    }
                }
            }
        }

        // Tìm phong cách trong tin nhắn ĐÃ LOẠI BỎ GIÁ
        $foundStyle = null;
        foreach ($styles as $style) {
            if (mb_strpos($messageWithoutPrice, $style) !== false) {
                $foundStyle = $style;
                break;
            }
        }

        // Tìm chất liệu trong tin nhắn ĐÃ LOẠI BỎ GIÁ
        $foundMaterial = null;
        foreach ($materials as $material) {
            if (mb_strpos($messageWithoutPrice, $material) !== false) {
                $foundMaterial = $material;
                break;
            }
        }

        // Xây dựng từ khóa tìm kiếm dựa trên các thông tin tìm được
        $keywords = [];

        if (!empty($foundCategories)) {
            $keywords = array_merge($keywords, $foundCategories);
        }

        if ($foundSpace) {
            // Ưu tiên dùng tên không gian gốc (VD: 'phòng khách') thay vì từ đồng nghĩa
            $originalSpaceName = array_search($foundSpaceKey, $this->spaceKeyMapping);
            $keywords[] = $originalSpaceName ?: $foundSpace; 
        }

        if ($foundStyle) {
            $keywords[] = $foundStyle;
        }

        if ($foundMaterial) {
            $keywords[] = $foundMaterial;
        }

        // Nếu không tìm thấy thông tin cụ thể (danh mục, không gian, style, material)
        if (empty($keywords)) {
            // AND nếu có thông tin giá được trích xuất
            if ($priceInfo['has_price']) {
                // Rất có thể người dùng CHỈ muốn lọc theo giá.
                // Trả về từ khóa rỗng để tránh tìm kiếm các từ còn sót lại như "các".
                Log::info("Không tìm thấy từ khóa cụ thể, chỉ có thông tin giá. Trả về từ khóa rỗng.");
                return [
                    'keywords' => '', // Trả về chuỗi rỗng
                    'price_info' => $priceInfo,
                    'space_key' => $foundSpaceKey
                ];
            } else {
                // Nếu không có từ khóa cụ thể VÀ KHÔNG có thông tin giá, xử lý message còn lại
                $processedMessage = $messageWithoutPrice;

                // Loại bỏ các stopwords
                foreach ($stopWords as $word) {
                    $processedMessage = preg_replace('/\b' . preg_quote($word, '/') . '\b/iu', '', $processedMessage);
                }

                $processedMessage = trim(preg_replace('/\s+/', ' ', $processedMessage));
                Log::info("Từ khóa cuối cùng sau khi xử lý stopwords: '{$processedMessage}'");

                // Nếu tin nhắn quá ngắn sau khi loại bỏ stopwords, trả về chuỗi rỗng
                if (mb_strlen($processedMessage) < 2) {
                    Log::warning("Từ khóa quá ngắn sau xử lý, trả về chuỗi rỗng.");
                    return [
                        'keywords' => '',
                        'price_info' => $priceInfo,
                        'space_key' => $foundSpaceKey
                    ];
                }

                return [
                    'keywords' => $processedMessage,
                    'price_info' => $priceInfo,
                    'space_key' => $foundSpaceKey
                ];
            }
        }

        // Kết hợp các từ khóa tìm được và loại bỏ trùng lặp
        $finalKeywords = implode(' ', array_unique($keywords));
        Log::info("Từ khóa cuối cùng từ các phần tử được trích xuất: '{$finalKeywords}'");
        
        return [
            'keywords' => $finalKeywords, 
            'price_info' => $priceInfo,
            'space_key' => $foundSpaceKey
        ];
    }

    /**
     * Trích xuất thông tin giá từ tin nhắn
     * 
     * @param string $message
     * @return array
     */
    private function extractPriceInfo($message)
    {
        // Chuẩn bị kết quả trả về
        $priceInfo = [
            'has_price' => false,
            'min_price' => null,
            'max_price' => null,
            'exact_price' => null,
            'price_type' => null // 'exact', 'range', 'min', 'max'
        ];
        
        // Chuyển tin nhắn về chữ thường
        $message = mb_strtolower($message, 'UTF-8');
        
        // Log tin nhắn để debug
        Log::info("Đang phân tích giá từ tin nhắn: {$message}");
        
        // 1. Tìm phạm vi giá (ưu tiên cao nhất)
        // Ví dụ: "từ 1 triệu đến 2 triệu", "1tr-2tr", "500k đến 1 triệu"
        $rangePatterns = [
            // "từ X đến Y"
            '/từ\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)\s+(?:đến|tới|tới|tới)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "X đến Y" (không có "từ")
            '/(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)\s+(?:đến|tới|tới|tới)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "X-Y" (dùng dấu gạch ngang)
            '/(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)\s*-\s*(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu'
        ];
        
        foreach ($rangePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $minPriceText = $matches[1];
                $maxPriceText = $matches[2];
                
                Log::info("Phát hiện phạm vi giá: '{$minPriceText}' đến '{$maxPriceText}'");
                
                // Chuyển đổi chuỗi giá thành số
                $minPrice = $this->convertPriceTextToNumber($minPriceText);
                $maxPrice = $this->convertPriceTextToNumber($maxPriceText);
                
                // Đảm bảo min luôn nhỏ hơn max
                if ($minPrice > $maxPrice) {
                    $temp = $minPrice;
                    $minPrice = $maxPrice;
                    $maxPrice = $temp;
                }
                
                // Cập nhật thông tin giá
                $priceInfo['has_price'] = true;
                $priceInfo['price_type'] = 'range';
                $priceInfo['min_price'] = $minPrice;
                $priceInfo['max_price'] = $maxPrice;
                
                Log::info("Đã chuyển đổi phạm vi giá: {$minPrice} VND đến {$maxPrice} VND");
                return $priceInfo;
            }
        }
        
        // 2. Tìm giá tối đa (nếu không tìm thấy phạm vi giá)
        // Ví dụ: "dưới 1 triệu", "không quá 500k", "tối đa 2tr", "giá dưới 1tr"
        $maxPricePatterns = [
            // "dưới X"
            '/(?:dưới|không quá|tối đa|chỉ|<=|<)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "dưới mức X"
            '/(?:dưới|không quá|tối đa|chỉ)\s+(?:mức|giá)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "X trở xuống"
            '/(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)\s+trở xuống/iu',
            // "giá dưới X" - pattern mới
            '/giá\s+(?:dưới|không quá|tối đa|chỉ)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu'
        ];
        
        foreach ($maxPricePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $priceText = $matches[1];
                Log::info("Phát hiện giá tối đa: '{$priceText}'");
                
                // Chuyển đổi chuỗi giá thành số
                $maxPrice = $this->convertPriceTextToNumber($priceText);
                
                // Cập nhật thông tin giá
                $priceInfo['has_price'] = true;
                $priceInfo['price_type'] = 'max';
                $priceInfo['max_price'] = $maxPrice;
                
                Log::info("Đã chuyển đổi giá tối đa: {$maxPrice} VND");
                return $priceInfo;
            }
        }
        
        // 3. Tìm giá tối thiểu (nếu không tìm thấy phạm vi giá và giá tối đa)
        // Ví dụ: "trên 1 triệu", "từ 500k trở lên", "tối thiểu 2tr"
        $minPricePatterns = [
            // "trên X"
            '/(?:trên|từ|tối thiểu|>=|>)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "trên mức X"
            '/(?:trên|từ|tối thiểu)\s+(?:mức|giá)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "X trở lên"
            '/(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)\s+trở lên/iu',
            // "giá trên X" - pattern mới
            '/giá\s+(?:trên|từ|tối thiểu)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu'
        ];
        
        foreach ($minPricePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $priceText = $matches[1];
                Log::info("Phát hiện giá tối thiểu: '{$priceText}'");
                
                // Chuyển đổi chuỗi giá thành số
                $minPrice = $this->convertPriceTextToNumber($priceText);
                
                // Cập nhật thông tin giá
                $priceInfo['has_price'] = true;
                $priceInfo['price_type'] = 'min';
                $priceInfo['min_price'] = $minPrice;
                
                Log::info("Đã chuyển đổi giá tối thiểu: {$minPrice} VND");
                return $priceInfo;
            }
        }
        
        // 4. Tìm giá chính xác (nếu không tìm thấy các loại giá khác)
        // Ví dụ: "giá 1 triệu", "giá khoảng 500k", "có giá 2tr"
        $exactPricePatterns = [
            // "giá X" hoặc "giá khoảng X"
            '/(?:giá|giá tiền|giá bán|giá cả|trị giá|có giá|mức giá)(?:\s+khoảng)?\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "khoảng X đồng" (không có từ "giá")
            '/khoảng\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "X đồng", "X VND" (số kèm đơn vị tiền tệ rõ ràng)
            '/(\d+[k\.]?\d*)\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)\b/iu',
            // Pattern mới cho "sản phẩm có giá X" và các biến thể
            '/(?:sản phẩm|sp|đồ|nội thất|hàng)(?:\s+\w+){0,3}\s+(?:có\s+)?giá\s+(?:là\s+)?(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // Pattern mới cho "có giá X" với "có" và "giá" cách xa nhau
            '/có\s+(?:\w+\s+){0,3}giá\s+(?:là\s+)?(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu'
        ];
        
        foreach ($exactPricePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $priceText = $matches[1];
                Log::info("Phát hiện giá chính xác: '{$priceText}'");
                
                // Kiểm tra tính hợp lệ của đơn vị tiền tệ nếu không rõ ràng
                $hasUnit = preg_match('/(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)/iu', $matches[0]);
                $isValidPrice = true;
                
                if (!$hasUnit && (int)$priceText < 1000) {
                    // Nếu số quá nhỏ và không có đơn vị, có thể là số ngẫu nhiên không phải giá
                    // Đảm bảo đây là số tiền chính xác
                    if ((int)$priceText > 100) {
                        // Nếu > 100, giả định đơn vị nghìn (VD: 150 -> 150,000 VND)
                        $priceText = $priceText . 'k';
                        Log::info("Đơn vị không rõ, giả định '{$priceText}' là nghìn đồng");
                    } else {
                        Log::info("Bỏ qua số quá nhỏ không có đơn vị: {$priceText}");
                        $isValidPrice = false;
                    }
                }
                
                // Chỉ xử lý nếu giá hợp lệ
                if ($isValidPrice) {
                    // Chuyển đổi chuỗi giá thành số
                    $exactPrice = $this->convertPriceTextToNumber($priceText);
                    
                    // Cập nhật thông tin giá
                    $priceInfo['has_price'] = true;
                    $priceInfo['price_type'] = 'exact';
                    $priceInfo['exact_price'] = $exactPrice;
                    
                    // Nếu có từ "khoảng", xác định phạm vi giá
                    if (mb_strpos($message, 'khoảng') !== false) {
                        $priceInfo['price_type'] = 'range';
                        $priceInfo['min_price'] = $exactPrice * 0.8; // Giảm 20%
                        $priceInfo['max_price'] = $exactPrice * 1.2; // Tăng 20%
                        
                        Log::info("Đã chuyển đổi giá khoảng: {$exactPrice} VND (phạm vi: {$priceInfo['min_price']} - {$priceInfo['max_price']} VND)");
                    } else {
                        Log::info("Đã chuyển đổi giá chính xác: {$exactPrice} VND");
                    }
                    
                    return $priceInfo;
                }
            }
        }
        
        // 5. Tìm số tiền đơn lẻ trong tin nhắn (ưu tiên thấp nhất)
        // Ví dụ: "500k", "1 triệu", "2tr", "150k" - mở rộng điều kiện áp dụng
        // Không chỉ khi có từ "giá" hoặc "bao nhiêu", mà còn khi có từ liên quan đến tìm kiếm sản phẩm
        $searchTerms = ['giá', 'bao nhiêu', 'tìm', 'sản phẩm', 'sp', 'nội thất', 'đồ', 'có', 'mua'];
        $shouldApplyMoneyPattern = false;
        
        foreach ($searchTerms as $term) {
            if (mb_strpos($message, $term) !== false) {
                $shouldApplyMoneyPattern = true;
                break;
            }
        }
        
        if ($shouldApplyMoneyPattern) {
            $moneyPattern = '/\b(\d+[k\.]?\d*)\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?\b/iu';
            
            if (preg_match($moneyPattern, $message, $matches)) {
                $priceText = $matches[1];
                Log::info("Phát hiện số tiền: '{$priceText}'");
                
                // Kiểm tra tính hợp lệ của đơn vị tiền tệ nếu không rõ ràng
                $hasUnit = preg_match('/(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)/iu', $matches[0]);
                $isValidPrice = true;
                
                if (!$hasUnit && (int)$priceText < 1000) {
                    // Nếu số quá nhỏ và không có đơn vị, có thể là số ngẫu nhiên không phải giá
                    // Đảm bảo đây là số tiền chính xác
                    if ((int)$priceText > 100) {
                        // Nếu > 100, giả định đơn vị nghìn (VD: 150 -> 150,000 VND)
                        $priceText = $priceText . 'k';
                        Log::info("Đơn vị không rõ, giả định '{$priceText}' là nghìn đồng");
                    } else {
                        Log::info("Bỏ qua số quá nhỏ không có đơn vị: {$priceText}");
                        $isValidPrice = false;
                    }
                }
                
                // Chỉ xử lý nếu giá hợp lệ
                if ($isValidPrice) {
                    // Chuyển đổi chuỗi giá thành số
                    $exactPrice = $this->convertPriceTextToNumber($priceText);
                    
                    // Cập nhật thông tin giá
                    $priceInfo['has_price'] = true;
                    $priceInfo['price_type'] = 'exact';
                    $priceInfo['exact_price'] = $exactPrice;
                    
                    Log::info("Đã chuyển đổi số tiền: {$exactPrice} VND");
                    return $priceInfo;
                }
            }
        }
        
        // Không tìm thấy thông tin giá
        Log::info("Không tìm thấy thông tin giá trong tin nhắn");
        return $priceInfo;
    }
    
    /**
     * Chuyển đổi chuỗi giá thành số
     * 
     * @param string $priceText
     * @return float
     */
    private function convertPriceTextToNumber($priceText)
    {
        // Loại bỏ dấu chấm ngăn cách hàng nghìn và khoảng trắng
        $priceText = trim($priceText);
        
        // Kiểm tra và xử lý đơn vị "nghìn" hoặc "k"
        if (preg_match('/(nghìn|ngàn|k)/iu', $priceText)) {
            $priceText = preg_replace('/(nghìn|ngàn|k)/iu', '', $priceText);
            $price = (float) preg_replace('/[^\d]/', '', $priceText);
            return $price * 1000;
        }
        
        // Kiểm tra và xử lý đơn vị "triệu" hoặc "tr"
        if (preg_match('/(triệu|tr)/iu', $priceText)) {
            $priceText = preg_replace('/(triệu|tr)/iu', '', $priceText);
            $price = (float) preg_replace('/[^\d]/', '', $priceText);
            return $price * 1000000;
        }
        
        // Nếu số đã có "k" ở cuối (ví dụ: 20k)
        if (preg_match('/(\d+)k$/iu', $priceText, $matches)) {
            return (float)$matches[1] * 1000;
        }
        
        // Loại bỏ tất cả ký tự không phải số
        $price = (float) preg_replace('/[^\d]/', '', $priceText);
        
        return $price;
    }

    /**
     * Tìm kiếm sản phẩm dựa trên từ khóa, tên sản phẩm, giá và không gian
     *
     * @param  string  $keywords Các từ khóa chung, có thể bao gồm tên sản phẩm, danh mục, không gian,...
     * @param  array   $priceInfo Thông tin về giá (min, max, exact)
     * @return array Kết quả tìm kiếm bao gồm sản phẩm và danh mục
     */
    private function searchProducts($keywords, $priceInfo = null)
    {
        try {
            Log::info('Tìm kiếm sản phẩm với từ khóa/tên: ' . $keywords);
            if ($priceInfo && $priceInfo['has_price']) {
                Log::info('Thông tin giá: ', $priceInfo);
            }

            // Tách từ khóa thành các phần riêng biệt
            $keywordParts = explode(' ', trim($keywords));

            // Lấy space_key từ phương thức extractSearchKeywords 
            $spaceKey = isset($priceInfo['space_key']) ? $priceInfo['space_key'] : null;

            // Ghi log space_key
            if ($spaceKey) {
                Log::info('Tìm kiếm sản phẩm theo không gian: ' . $spaceKey);
            }

            // Bắt đầu truy vấn
            $query = Product::with(['category', 'gallery', 'variants']);

            // Kiểm tra xem có phải tìm kiếm theo tên sản phẩm cụ thể không
            $isProductNameSearch = false;
            $potentialProductName = trim($keywords); 
            $commonSearchTerms = ['phòng', 'không gian', 'khu vực', 'nơi', 'bàn', 'ghế', 'tủ', 'giường', 'kệ', 'đèn', 'thảm', 'gương', 'sofa', 'giá'];
            
            if (count($keywordParts) > 1) {
                $isProductNameSearch = true;
                foreach ($commonSearchTerms as $term) {
                    if (mb_strpos(mb_strtolower($potentialProductName, 'UTF-8'), $term) !== false) {
                        $isProductNameSearch = false;
                        break;
                    }
                }
                if ($isProductNameSearch) { // Chỉ kiểm tra space key nếu vẫn còn khả năng là tên sản phẩm
                    foreach (array_keys($this->spaceKeyMapping) as $spaceText) {
                        if (mb_strpos(mb_strtolower($potentialProductName, 'UTF-8'), $spaceText) !== false) {
                            $isProductNameSearch = false;
                            break;
                        }
                    }
                }
            }

            // Ưu tiên tìm kiếm theo tên sản phẩm nếu được xác định
            if ($isProductNameSearch) {
                Log::info('Ưu tiên tìm kiếm theo tên sản phẩm: ' . $potentialProductName);
                $query->where(function($q) use ($potentialProductName) {
                    $q->where('name', 'like', '%' . $potentialProductName . '%');
                });
            } else {
                 Log::info('Thực hiện tìm kiếm theo từ khóa chung, không gian và danh mục.');
                // Nếu không phải tìm kiếm theo tên, tiếp tục logic cũ
                
                // Nếu có space_key, lọc sản phẩm theo danh mục thuộc không gian đó
                if ($spaceKey) {
                    $categoryIdsInSpace = DB::table('category_space')
                                            ->where('space_key', $spaceKey)
                                            ->pluck('category_id')
                                            ->unique()
                                            ->toArray();
                    
                    if (!empty($categoryIdsInSpace)) {
                        Log::info('Lọc theo các danh mục thuộc không gian: ' . $spaceKey . ', IDs: ' . implode(', ', $categoryIdsInSpace));
                        $query->whereIn('category_id', $categoryIdsInSpace);
                    }
                }

                // Ánh xạ giữa không gian và các loại sản phẩm phù hợp
                 $spaceToProductMapping = [
                    'phòng khách' => ['sofa', 'ghế sofa', 'bàn trà', 'bàn cafe', 'kệ tivi', 'kệ trang trí', 'đèn', 'thảm', 'gối trang trí', 'bàn bên', 'ghế đôn'],
                    'phòng ngủ' => ['giường', 'tủ quần áo', 'tủ đầu giường', 'đèn ngủ', 'gương', 'ghế trang điểm', 'bàn trang điểm', 'tủ trang điểm'],
                    'phòng ăn' => ['bàn ăn', 'ghế ăn', 'tủ bếp', 'đèn bàn ăn', 'kệ bếp', 'tủ ly', 'bàn đảo bếp'],
                    'phòng làm việc' => ['bàn làm việc', 'ghế văn phòng', 'kệ sách', 'đèn bàn', 'tủ tài liệu', 'bàn máy tính'],
                    'ban công' => ['ghế ngoài trời', 'bàn ngoài trời', 'ghế thư giãn', 'đèn ngoài trời'],
                    'phòng tắm' => ['kệ phòng tắm', 'gương phòng tắm', 'tủ lavabo', 'giá treo khăn']
                ];
                
                // Ánh xạ từ khóa không gian vào tiếng Anh để tìm kiếm rộng hơn
                $spaceMapping = [
                    'phòng khách' => ['living room', 'lounge'],
                    'phòng ngủ' => ['bedroom', 'sleeping room'],
                    'phòng ăn' => ['dining room', 'kitchen'],
                    'phòng làm việc' => ['office', 'workspace', 'study room', 'working room'],
                    'ban công' => ['balcony', 'terrace', 'patio'],
                    'phòng tắm' => ['bathroom', 'restroom']
                ];

                // Kiểm tra xem từ khóa có chứa không gian không
                $detectedSpaces = [];
                foreach ($spaceToProductMapping as $space => $products) {
                    if (mb_strpos(mb_strtolower($keywords, 'UTF-8'), $space) !== false) {
                        $detectedSpaces[] = $space;
                    }
                }
                
                if ($spaceKey && empty($detectedSpaces)) {
                    $spaceName = $this->getSpaceNameFromKey($spaceKey);
                    if ($spaceName && $spaceName !== $spaceKey) {
                        $detectedSpaces[] = $spaceName;
                        Log::info('Thêm không gian từ space_key: ' . $spaceName);
                    }
                }

                // Các từ khóa thể hiện tìm kiếm theo không gian
                $spaceSearchTerms = ['phòng', 'không gian', 'khu vực', 'nơi'];
                $isSpaceSearch = false;
                
                foreach ($spaceSearchTerms as $term) {
                    if (mb_strpos(mb_strtolower($keywords, 'UTF-8'), $term) !== false) {
                        $isSpaceSearch = true;
                        break;
                    }
                }
                
                if ($spaceKey && !$isSpaceSearch) {
                    $isSpaceSearch = true;
                    Log::info('Đánh dấu là tìm kiếm theo không gian dựa trên space_key');
                }

                // Kiểm tra xem có từ khóa là danh mục sản phẩm cụ thể không
                $productCategories = [
                    'bàn' => ['bàn', 'table', 'desk'],
                    'ghế' => ['ghế', 'chair', 'stool'],
                    'sofa' => ['sofa', 'ghế sofa', 'couch'],
                    'giường' => ['giường', 'bed', 'mattress'],
                    'tủ' => ['tủ', 'cabinet', 'wardrobe', 'closet'],
                    'đèn' => ['đèn', 'lamp', 'light'],
                    'gương' => ['gương', 'mirror'],
                    'thảm' => ['thảm', 'carpet', 'rug'],
                    'kệ' => ['kệ', 'shelf', 'shelve', 'rack']
                ];
                
                $specificCategories = [];
                $relatedProductTerms = [];
                
                // Nếu tìm thấy từ khóa không gian, thêm các sản phẩm liên quan
                if (!empty($detectedSpaces) || $isSpaceSearch) {
                     Log::info('Phát hiện tìm kiếm theo không gian: ' . implode(', ', $detectedSpaces));
                    
                    foreach ($detectedSpaces as $space) {
                        if (isset($spaceToProductMapping[$space])) {
                            $relatedProductTerms = array_merge($relatedProductTerms, $spaceToProductMapping[$space]);
                            if (isset($spaceMapping[$space])) {
                                $keywordParts = array_merge($keywordParts, $spaceMapping[$space]);
                            }
                        }
                    }
                    
                    if (empty($detectedSpaces) && $isSpaceSearch && !$spaceKey) {
                        $possibleSpaceHints = [];
                        $spaceHints = [
                            'khách' => 'phòng khách', 'ngủ' => 'phòng ngủ', 'ăn' => 'phòng ăn',
                            'làm việc' => 'phòng làm việc', 'tắm' => 'phòng tắm', 'bếp' => 'phòng ăn',
                            'nấu ăn' => 'phòng ăn', 'tiếp khách' => 'phòng khách'
                        ];
                        foreach ($spaceHints as $hint => $space) {
                            if (mb_strpos(mb_strtolower($keywords, 'UTF-8'), $hint) !== false) {
                                $possibleSpaceHints[] = $space;
                            }
                        }
                        foreach (array_unique($possibleSpaceHints) as $space) {
                             if (isset($spaceToProductMapping[$space])) {
                                $relatedProductTerms = array_merge($relatedProductTerms, $spaceToProductMapping[$space]);
                                if (isset($spaceMapping[$space])) {
                                    $keywordParts = array_merge($keywordParts, $spaceMapping[$space]);
                                }
                            }
                        }
                        if (empty($possibleSpaceHints) && mb_strpos(mb_strtolower($keywords, 'UTF-8'), 'phòng') !== false) {
                            Log::info('Không xác định được loại phòng cụ thể, mặc định là phòng khách');
                            $detectedSpaces[] = 'phòng khách';
                            $relatedProductTerms = array_merge($relatedProductTerms, $spaceToProductMapping['phòng khách']);
                            $keywordParts = array_merge($keywordParts, $spaceMapping['phòng khách']);
                        }
                    }
                    
                    $relatedProductTerms = array_unique($relatedProductTerms);
                    Log::info('Mở rộng tìm kiếm sang các sản phẩm liên quan đến không gian: ' . implode(', ', $relatedProductTerms));
                    
                    foreach ($relatedProductTerms as $term) {
                        $isCategoryTerm = false;
                        foreach ($productCategories as $category => $terms) {
                            if (in_array($term, $terms) || mb_strpos($term, $category) !== false) {
                                if (!in_array($category, $specificCategories)) {
                                    $specificCategories[] = $category;
                                    $isCategoryTerm = true;
                                }
                            }
                        }
                        if (!$isCategoryTerm) {
                            $keywordParts[] = $term;
                        }
                    }
                    $keywordParts = array_unique($keywordParts);
                }
                
                // Tìm tất cả danh mục sản phẩm trong từ khóa ban đầu
                foreach ($productCategories as $category => $terms) {
                    foreach ($terms as $term) {
                        if (mb_strpos(mb_strtolower($keywords, 'UTF-8'), $term) !== false) {
                            if (!in_array($category, $specificCategories)) {
                                $specificCategories[] = $category;
                            }
                        }
                    }
                }
                $specificCategories = array_unique($specificCategories);

                // Xây dựng điều kiện WHERE phức tạp
                $query->where(function($mainQuery) use ($specificCategories, $productCategories, $keywordParts, $keywords) {
                    // 1. Lọc theo danh mục cụ thể nếu có
                    if (!empty($specificCategories)) {
                        Log::info('Lọc theo danh mục cụ thể: ' . implode(', ', $specificCategories));
                        $mainQuery->whereHas('category', function($q) use ($specificCategories, $productCategories) {
                            $q->where(function($subQ) use ($specificCategories, $productCategories) {
                                foreach ($specificCategories as $category) {
                                    $categoryTerms = $productCategories[$category] ?? [$category];
                                    foreach ($categoryTerms as $term) {
                                        $subQ->orWhere('name', 'like', '%' . $term . '%');
                                    }
                                }
                            });
                        });
                    }

                    // 2. Tìm kiếm theo các từ khóa còn lại
                    $searchableParts = array_filter($keywordParts, function($part) {
                         return mb_strlen(trim($part)) >= 2; // Chỉ tìm từ >= 2 ký tự
                    });

                    if (empty($specificCategories) || !empty($searchableParts)) {
                        Log::info('Tìm kiếm theo các từ khóa: ' . implode(', ', $searchableParts));
                         $mainQuery->orWhere(function ($q) use ($searchableParts) {
                             foreach ($searchableParts as $part) {
                                 $q->orWhere('name', 'like', '%' . $part . '%')
                                   ->orWhere('description', 'like', '%' . $part . '%')
                                   ->orWhereHas('category', function($categoryQuery) use ($part) {
                                       $categoryQuery->where('name', 'like', '%' . $part . '%');
                                   });
                             }
                         });
                    } elseif (!empty($specificCategories) && empty($searchableParts) && mb_strlen(trim($keywords)) >= 2) {
                        // Trường hợp chỉ có danh mục và từ khóa gốc đủ dài (nhưng keywordParts đã bị lọc hết)
                         Log::info('Tìm kiếm thêm từ khóa gốc "' . $keywords . '" trong tên/mô tả cho danh mục đã lọc.');
                         $mainQuery->orWhere(function ($q) use ($keywords) {
                            $q->where('name', 'like', '%' . $keywords . '%')
                              ->orWhere('description', 'like', '%' . $keywords . '%');
                        });
                    }
                });
            } // Kết thúc else của isProductNameSearch

            // Clone query trước khi áp dụng bộ lọc giá để kiểm tra sau
            $queryBeforePriceFilter = clone $query;

            // Áp dụng bộ lọc giá
            if ($priceInfo && $priceInfo['has_price']) {
                Log::info('Đang áp dụng bộ lọc giá...');
                
                // Xử lý giá chính xác (với khoảng dao động nhỏ)
                if ($priceInfo['price_type'] === 'exact' && $priceInfo['exact_price'] !== null) {
                    $exactPrice = $priceInfo['exact_price'];
                    $minPrice = $exactPrice * 0.85; 
                    $maxPrice = $exactPrice * 1.15; 
                    Log::info("Lọc sản phẩm với giá chính xác: {$exactPrice} VND, phạm vi [{$minPrice} - {$maxPrice}] VND");
                    $this->applyPriceBetweenFilter($query, $minPrice, $maxPrice); // Apply directly
                }
                // Xử lý phạm vi giá
                elseif ($priceInfo['price_type'] === 'range' && $priceInfo['min_price'] !== null && $priceInfo['max_price'] !== null) {
                    $minPrice = $priceInfo['min_price'];
                    $maxPrice = $priceInfo['max_price'];
                    Log::info("Lọc sản phẩm với phạm vi giá: [{$minPrice} - {$maxPrice}] VND");
                    $this->applyPriceBetweenFilter($query, $minPrice, $maxPrice); // Apply directly
                }
                // Xử lý giá tối đa
                elseif ($priceInfo['price_type'] === 'max' && $priceInfo['max_price'] !== null) {
                    $maxPrice = $priceInfo['max_price'];
                    Log::info("Lọc sản phẩm với giá tối đa: {$maxPrice} VND");
                    $this->applyPriceMaxFilter($query, $maxPrice); // Apply directly
                }
                // Xử lý giá tối thiểu
                elseif ($priceInfo['price_type'] === 'min' && $priceInfo['min_price'] !== null) {
                    $minPrice = $priceInfo['min_price'];
                    Log::info("Lọc sản phẩm với giá tối thiểu: {$minPrice} VND");
                    $this->applyPriceMinFilter($query, $minPrice); // Apply directly
                }
            }

            // Log the SQL query and bindings before execution
            Log::info('Generated SQL Query: ' . $query->toSql());
            Log::info('Query Bindings: ', $query->getBindings());

            // Lấy kết quả
            $products = $query->orderBy('created_at', 'desc')
                ->limit(50) // Tăng giới hạn tạm thời để kiểm tra
                ->get();

            Log::info('Tìm thấy ' . $products->count() . ' sản phẩm sau khi lọc.');
            
            // Debug chi tiết các sản phẩm tìm thấy
            foreach ($products as $product) {
                $hasVariants = $product->variants->isNotEmpty();
                $productPrice = $product->price;
                $productDiscountPrice = $product->discount_price;
                $displayPrice = $productDiscountPrice ?? $productPrice;
                
                Log::info("Sản phẩm: {$product->name}, ID: {$product->id}, Giá: {$displayPrice}, Có biến thể: " . ($hasVariants ? 'Có' : 'Không'));
                
                if ($hasVariants) {
                    foreach ($product->variants as $index => $variant) {
                        $variantPrice = $variant->price;
                        $variantDiscountPrice = $variant->discount_price;
                        $variantDisplayPrice = $variantDiscountPrice ?? $variantPrice;
                        Log::info("  - Biến thể #{$index}: ID: {$variant->id}, Giá: {$variantDisplayPrice}");
                    }
                }
            }

            // Kiểm tra xem có sản phẩm nào phù hợp với khoảng giá yêu cầu không
            // Đơn giản hóa logic: Nếu $products không rỗng sau khi lọc giá (nếu có), 
            // thì tức là có sản phẩm trong khoảng giá đó.
            $hasProductsInPriceRange = $products->isNotEmpty();
            if ($priceInfo && $priceInfo['has_price']) {
                Log::info('Kiểm tra sản phẩm trong khoảng giá yêu cầu (sau khi lọc): ' . ($hasProductsInPriceRange ? 'Có' : 'Không'));
            } // Không cần else vì nếu không lọc giá, hasProductsInPriceRange chỉ đơn giản là $products có rỗng không.
            
            // Phân loại sản phẩm theo danh mục và định dạng lại
            $categorizedProducts = [];
            $foundCategories = [];
            $productsInPriceRangeList = []; 

            $formattedProducts = $products->map(function ($product) use (&$categorizedProducts, &$foundCategories, &$productsInPriceRangeList, $priceInfo) {
                $imagePath = $product->image_thumnail ?? null;
                $categoryName = $product->category ? $product->category->name : 'N/A';
                
                if (!in_array($categoryName, $foundCategories)) {
                    $foundCategories[] = $categoryName;
                }
                
                $displayPrice = 0;
                $productPrice = 0;
                $productDiscountPrice = null;
                $hasVariants = $product->variants->isNotEmpty();
                
                if ($hasVariants) {
                    // Thay đổi ở đây: Kiểm tra tất cả các biến thể và chọn biến thể phù hợp nhất với điều kiện giá
                    // Nếu có điều kiện giá, ưu tiên biến thể nằm trong khoảng giá
                    if ($priceInfo && $priceInfo['has_price']) {
                        $matchingVariants = [];
                        foreach ($product->variants as $variant) {
                            $variantPrice = $variant->price;
                            $variantDiscountPrice = $variant->discount_price;
                            $effectivePrice = $variantDiscountPrice ?? $variantPrice;
                            
                            $matchesPrice = false;
                            if ($priceInfo['price_type'] === 'exact' && $priceInfo['exact_price'] !== null) {
                                $exactPrice = $priceInfo['exact_price'];
                                $minPrice = $exactPrice * 0.85;
                                $maxPrice = $exactPrice * 1.15;
                                $matchesPrice = ($effectivePrice >= $minPrice && $effectivePrice <= $maxPrice);
                            } 
                            else if ($priceInfo['price_type'] === 'range' && $priceInfo['min_price'] !== null && $priceInfo['max_price'] !== null) {
                                $matchesPrice = ($effectivePrice >= $priceInfo['min_price'] && $effectivePrice <= $priceInfo['max_price']);
                            }
                            else if ($priceInfo['price_type'] === 'max' && $priceInfo['max_price'] !== null) {
                                $matchesPrice = ($effectivePrice <= $priceInfo['max_price']);
                            }
                            else if ($priceInfo['price_type'] === 'min' && $priceInfo['min_price'] !== null) {
                                $matchesPrice = ($effectivePrice >= $priceInfo['min_price']);
                            }
                            
                            // Log chi tiết về biến thể
                            $variantPriceInfo = "Biến thể ID {$variant->id}: Giá {$variantPrice}, Giá KM " . 
                                            ($variantDiscountPrice ? $variantDiscountPrice : "không có") . 
                                            ", Giá hiệu lực {$effectivePrice}";
                            
                            if ($matchesPrice) {
                                Log::info("SP {$product->id} - {$product->name}: {$variantPriceInfo} - KHỚP VỚI ĐIỀU KIỆN GIÁ");
                                $matchingVariants[] = [
                                    'variant' => $variant,
                                    'price' => $effectivePrice
                                ];
                            } else {
                                Log::info("SP {$product->id} - {$product->name}: {$variantPriceInfo} - KHÔNG KHỚP VỚI ĐIỀU KIỆN GIÁ");
                            }
                        }
                        
                        // Nếu có biến thể phù hợp với giá, chọn biến thể đầu tiên (sau khi sắp xếp theo giá)
                        if (!empty($matchingVariants)) {
                            // Sắp xếp theo giá tăng dần (cho max, exact, range) hoặc giảm dần (cho min)
                            if ($priceInfo['price_type'] === 'min') {
                                usort($matchingVariants, function($a, $b) {
                                    return $b['price'] - $a['price']; // Sắp xếp giảm dần
                                });
                            } else {
                                usort($matchingVariants, function($a, $b) {
                                    return $a['price'] - $b['price']; // Sắp xếp tăng dần
                                });
                            }
                            
                            $bestVariant = $matchingVariants[0]['variant'];
                            $productPrice = $bestVariant->price;
                            $productDiscountPrice = $bestVariant->discount_price;
                            $displayPrice = $productDiscountPrice ?? $productPrice;
                        } else {
                            // Nếu không có biến thể nào khớp với điều kiện giá, chọn biến thể có giá thấp nhất
                            $lowestPriceVariant = $product->variants->sortBy(function($variant) {
                                return $variant->discount_price ?? $variant->price;
                            })->first();
                            
                            if ($lowestPriceVariant) {
                                $productPrice = $lowestPriceVariant->price;
                                $productDiscountPrice = $lowestPriceVariant->discount_price;
                                $displayPrice = $productDiscountPrice ?? $productPrice;
                            }
                        }
                    } else {
                        // Nếu không có điều kiện giá, chọn biến thể có giá thấp nhất như cũ
                        $lowestPriceVariant = $product->variants->sortBy(function($variant) {
                            return $variant->discount_price ?? $variant->price;
                        })->first();
                        
                        if ($lowestPriceVariant) {
                            $productPrice = $lowestPriceVariant->price;
                            $productDiscountPrice = $lowestPriceVariant->discount_price;
                            $displayPrice = $productDiscountPrice ?? $productPrice;
                        }
                    }
                } else {
                    $productPrice = $product->price;
                    $productDiscountPrice = $product->discount_price;
                    $displayPrice = $productDiscountPrice ?? $productPrice;
                }
                 
                 $formattedProduct = [
                     'id' => $product->id,
                     'name' => $product->name,
                     'price' => $productPrice,
                     'discount_price' => $productDiscountPrice,
                     'image' => $imagePath,
                     'category' => $categoryName,
                     'category_group' => $categoryName,
                     'description' => Str::limit($product->description, 100),
                     'display_price' => $displayPrice,
                     'has_variants' => $hasVariants,
                     'variant_count' => $product->variants->count(),
                     'variants' => $hasVariants ? $product->variants->map(function($variant) {
                         return [
                             'id' => $variant->id,
                             'price' => $variant->price,
                             'discount_price' => $variant->discount_price,
                             'variant_details' => $variant->variant_details ?? null 
                         ];
                     })->toArray() : []
                 ];

                if (!isset($categorizedProducts[$categoryName])) {
                    $categorizedProducts[$categoryName] = [];
                }
                $categorizedProducts[$categoryName][] = $formattedProduct;
                $productsInPriceRangeList[] = $formattedProduct; 

                return $formattedProduct;

            })->values()->all(); // Ensure it's a plain array

            // --- Bắt đầu thay đổi logic --- 

            // Gán trực tiếp kết quả map vào $sortedProducts
            $sortedProducts = $formattedProducts;

            // Kiểm tra lại $hasProductsInPriceRange dựa trên kết quả cuối cùng
            if ($priceInfo && $priceInfo['has_price']) {
                // Nếu $sortedProducts rỗng sau khi map (nghĩa là không có SP nào/biến thể nào khớp giá ban đầu)
                // thì $hasProductsInPriceRange phải là false.
                $hasProductsInPriceRange = !empty($sortedProducts);
                Log::info('Kiểm tra lại hasProductsInPriceRange sau khi map: ' . ($hasProductsInPriceRange ? 'Có' : 'Không'));

                // Nếu vẫn có sản phẩm, tiến hành sắp xếp
                if ($hasProductsInPriceRange) {
                    $sortFunction = function($a, $b) {
                        // Sử dụng display_price đã tính toán trong map
                        $priceA = $a['display_price'];
                        $priceB = $b['display_price'];
                        return $priceA - $priceB; // Sắp xếp tăng dần mặc định
                    };

                    if ($priceInfo['price_type'] === 'min') {
                        // Sắp xếp giảm dần cho min price
                        usort($sortedProducts, function($a, $b) {
                            $priceA = $a['display_price'];
                            $priceB = $b['display_price'];
                            return $priceB - $priceA;
                        });
                        Log::info('Đã sắp xếp sản phẩm giảm dần theo display_price.');
                    } else if (in_array($priceInfo['price_type'], ['max', 'range', 'exact'])) {
                        // Sắp xếp tăng dần cho các trường hợp còn lại
                        usort($sortedProducts, $sortFunction);
                        Log::info('Đã sắp xếp sản phẩm tăng dần theo display_price.');
                    }
                }
            } else {
                // Nếu không có lọc giá, sắp xếp theo thứ tự mặc định (có thể là ID hoặc tên)
                 usort($sortedProducts, function($a, $b) {
                     return $a['id'] - $b['id']; // Ví dụ: sắp xếp theo ID
                 });
                 Log::info('Không có lọc giá, sắp xếp sản phẩm theo ID.');
            }
            
            // --- Kết thúc thay đổi logic ---

            Log::info('Số sản phẩm cuối cùng trả về: ' . count($sortedProducts));
            
            // Log chi tiết cấu trúc $sortedProducts trước khi return
            Log::info('Cấu trúc dữ liệu sản phẩm trả về:', $sortedProducts);

            // Nhóm lại sản phẩm theo danh mục sau khi đã sắp xếp
            $sortedCategorizedProducts = [];
            foreach ($sortedProducts as $product) {
                $catName = $product['category'];
                if (!isset($sortedCategorizedProducts[$catName])) {
                    $sortedCategorizedProducts[$catName] = [];
                }
                $sortedCategorizedProducts[$catName][] = $product;
            }

            return [
                'products' => $sortedProducts,
                'categories' => array_values(array_unique($foundCategories)),
                'categorized_products' => $sortedCategorizedProducts, 
                'has_products_in_price_range' => $hasProductsInPriceRange
            ];
        } catch (\Exception $e) {
            Log::error('Error searching products: ' . $e->getMessage() . ' on line ' . $e->getLine());
            Log::error($e->getTraceAsString());
            return [
                'products' => [], 'categories' => [], 'categorized_products' => [], 'has_products_in_price_range' => false
            ];
        }
    }

     /**
      * Helper function to apply 'between' price filter to product/variant prices.
      * Cập nhật: Đảm bảo logic OR hoạt động đúng giữa giá gốc và giá giảm giá.
      * Cập nhật 2: Ưu tiên giá khuyến mãi khi lọc.
      *
      * @param \Illuminate\Database\Eloquent\Builder $query
      * @param float $minPrice
      * @param float $maxPrice
      * @return void
      */
     private function applyPriceBetweenFilter($query, $minPrice, $maxPrice)
     {
         $query->where(function($subQ) use ($minPrice, $maxPrice) {
             // Điều kiện cho sản phẩm chính
             $subQ->where(function($productPriceQuery) use ($minPrice, $maxPrice) {
                 // Giá hiệu lực (ưu tiên discount_price) nằm trong khoảng [minPrice, maxPrice]
                 $productPriceQuery->where(function($effectivePriceQ) use ($minPrice, $maxPrice) {
                     // Có discount_price và nó nằm trong khoảng
                     $effectivePriceQ->whereNotNull('discount_price')
                                     ->where('discount_price', '>=', $minPrice)
                                     ->where('discount_price', '<=', $maxPrice);
                 })->orWhere(function($basePriceQ) use ($minPrice, $maxPrice) {
                     // Không có discount_price HOẶC discount_price không trong khoảng,
                     // VÀ giá gốc nằm trong khoảng
                     $basePriceQ->whereNull('discount_price') // Chỉ áp dụng giá gốc khi không có giá KM
                                ->where('price', '>=', $minPrice)
                                ->where('price', '<=', $maxPrice);
                 });
             })
             // HOẶC điều kiện cho bất kỳ biến thể nào
             ->orWhereHas('variants', function($variantQuery) use ($minPrice, $maxPrice) {
                 // Giá hiệu lực của biến thể nằm trong khoảng
                 $variantQuery->where(function($variantPriceQuery) use ($minPrice, $maxPrice) {
                     $variantPriceQuery->where(function($effectivePriceQ) use ($minPrice, $maxPrice) {
                         $effectivePriceQ->whereNotNull('discount_price')
                                         ->where('discount_price', '>=', $minPrice)
                                         ->where('discount_price', '<=', $maxPrice);
                     })->orWhere(function($basePriceQ) use ($minPrice, $maxPrice) {
                         $basePriceQ->whereNull('discount_price')
                                    ->where('price', '>=', $minPrice)
                                    ->where('price', '<=', $maxPrice);
                     });
                 });
             });
         });
         
         // Log để debug
         Log::info("Áp dụng lọc giá BETWEEN: $minPrice - $maxPrice");
     }

     /**
      * Helper function to apply 'max' price filter to product/variant prices.
      * Cập nhật: Ưu tiên giá khuyến mãi khi lọc.
      *
      * @param \Illuminate\Database\Eloquent\Builder $query
      * @param float $maxPrice
      * @return void
      */
     private function applyPriceMaxFilter($query, $maxPrice)
     {
         $query->where(function($subQ) use ($maxPrice) {
             // Điều kiện cho sản phẩm chính
             $subQ->where(function($productPriceQuery) use ($maxPrice) {
                 // Giá hiệu lực <= maxPrice
                 $productPriceQuery->where(function($effectivePriceQ) use ($maxPrice) {
                     // Có discount_price và nó <= maxPrice
                     $effectivePriceQ->whereNotNull('discount_price')
                                     ->where('discount_price', '<=', $maxPrice);
                 })->orWhere(function($basePriceQ) use ($maxPrice) {
                     // Không có discount_price VÀ giá gốc <= maxPrice
                     $basePriceQ->whereNull('discount_price')
                                ->where('price', '<=', $maxPrice);
                 });
             })
             // HOẶC điều kiện cho bất kỳ biến thể nào
             ->orWhereHas('variants', function($variantQuery) use ($maxPrice) {
                 // Giá hiệu lực của biến thể <= maxPrice
                 $variantQuery->where(function($variantPriceQuery) use ($maxPrice) {
                     $variantPriceQuery->where(function($effectivePriceQ) use ($maxPrice) {
                         $effectivePriceQ->whereNotNull('discount_price')
                                         ->where('discount_price', '<=', $maxPrice);
                     })->orWhere(function($basePriceQ) use ($maxPrice) {
                         $basePriceQ->whereNull('discount_price')
                                    ->where('price', '<=', $maxPrice);
                     });
                 });
             });
         });
         
         // Log để debug
         Log::info("Áp dụng lọc giá MAX: $maxPrice");
     }

     /**
      * Helper function to apply 'min' price filter to product/variant prices.
      * Cập nhật: Ưu tiên giá khuyến mãi khi lọc.
      *
      * @param \Illuminate\Database\Eloquent\Builder $query
      * @param float $minPrice
      * @return void
      */
     private function applyPriceMinFilter($query, $minPrice)
     {
         $query->where(function($subQ) use ($minPrice) {
             // Điều kiện cho sản phẩm chính
             $subQ->where(function($productPriceQuery) use ($minPrice) {
                 // Giá hiệu lực >= minPrice
                 $productPriceQuery->where(function($effectivePriceQ) use ($minPrice) {
                     // Có discount_price và nó >= minPrice
                     $effectivePriceQ->whereNotNull('discount_price')
                                     ->where('discount_price', '>=', $minPrice);
                 })->orWhere(function($basePriceQ) use ($minPrice) {
                     // Không có discount_price VÀ giá gốc >= minPrice
                     $basePriceQ->whereNull('discount_price')
                                ->where('price', '>=', $minPrice);
                 });
             })
             // HOẶC điều kiện cho bất kỳ biến thể nào
             ->orWhereHas('variants', function($variantQuery) use ($minPrice) {
                 // Giá hiệu lực của biến thể >= minPrice
                 $variantQuery->where(function($variantPriceQuery) use ($minPrice) {
                     $variantPriceQuery->where(function($effectivePriceQ) use ($minPrice) {
                         $effectivePriceQ->whereNotNull('discount_price')
                                         ->where('discount_price', '>=', $minPrice);
                     })->orWhere(function($basePriceQ) use ($minPrice) {
                         $basePriceQ->whereNull('discount_price')
                                    ->where('price', '>=', $minPrice);
                     });
                 });
             });
         });
         
         // Log để debug
         Log::info("Áp dụng lọc giá MIN: $minPrice");
     }

    /**
     * Gọi API Gemini để xử lý tin nhắn
     *
     * @param  string  $message
     * @param  array  $context  Thông tin bổ sung về ngữ cảnh tin nhắn
     * @return string
     */
    private function callGeminiApi($message, $context = [])
    {
        try {
            // Lấy API key từ biến môi trường
            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                throw new \Exception('GEMINI_API_KEY không được cấu hình trong file .env');
            }

            // Endpoint của Gemini API
            $endpoint = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent';

            // Prompt chuyên gia về nội thất
            $expertPrompt = "Bạn là một chuyên gia tư vấn về nội thất và thiết kế nội thất của Eco-Furnish - một cửa hàng chuyên cung cấp đồ nội thất thân thiện với môi trường, bền vững và hiện đại. Hãy trả lời các câu hỏi của khách hàng một cách chuyên nghiệp, thân thiện và hữu ích.

Kiến thức chuyên môn của bạn bao gồm:
1. Các loại đồ nội thất: ghế, bàn, giường, tủ, kệ, đèn, thảm và các phụ kiện trang trí
2. Chất liệu bền vững: gỗ tái chế, tre, mây, vải hữu cơ, kim loại tái chế
3. Phong cách thiết kế: Scandinavian, Minimalist, Modern, Industrial, Bohemian
4. Tư vấn bố trí không gian: phòng khách, phòng ngủ, phòng ăn, văn phòng tại nhà
5. Bảo quản và vệ sinh đồ nội thất
6. Xu hướng thiết kế nội thất hiện đại
7. Lợi ích của đồ nội thất thân thiện với môi trường

Khi trả lời:
- Luôn giới thiệu bản thân là trợ lý AI của Eco-Furnish
- Sử dụng ngôn ngữ thân thiện, chuyên nghiệp và dễ hiểu
- PHẢI trả lời NGẮN GỌN và TRỰC TIẾP, tối đa 1-2 câu
- TUYỆT ĐỐI KHÔNG trả lời dài dòng, kể cả khi đưa ra lời khuyên
- KHÔNG đưa ra quá nhiều chi tiết không cần thiết
- KHÔNG đưa ra giải thích dài dòng về sản phẩm hoặc dịch vụ
- Đưa ra lời khuyên cụ thể và thực tế
- Nếu không biết câu trả lời, hãy thành thật và đề nghị khách hàng liên hệ với nhân viên tư vấn
- Không đưa ra thông tin sai lệch về sản phẩm hoặc dịch vụ";


            // Nếu đây là yêu cầu tìm kiếm sản phẩm, thêm hướng dẫn cụ thể
            if (isset($context['is_product_search']) && $context['is_product_search']) {
                $searchKeywords = $context['search_keywords'] ?? '';
                $foundProducts = $context['found_products'] ?? false;
                $productCount = $context['product_count'] ?? 0;
                $categoriesFound = $context['categories_found'] ?? [];
                $priceInfo = $context['price_info'] ?? null;
                $hasProductsInPriceRange = isset($context['has_products_in_price_range']) ? $context['has_products_in_price_range'] : false;
                $spaceKey = $context['space_key'] ?? null;
                
                $expertPrompt .= "\n\nĐây là yêu cầu tìm kiếm sản phẩm với từ khóa: \"$searchKeywords\".";
                
                // Thêm thông tin về không gian nếu có
                if ($spaceKey) {
                    $spaceName = $this->getSpaceNameFromKey($spaceKey);
                    if ($spaceName !== $spaceKey) { // Chỉ thêm nếu dịch được
                         $expertPrompt .= " Khách hàng đang tìm sản phẩm cho \"$spaceName\".";
                    } else {
                         $expertPrompt .= " Khách hàng đang tìm sản phẩm liên quan đến \"$spaceKey\"."; // Nếu không dịch được, dùng key
                    }
                }
                
                // Thêm thông tin về giá nếu có
                if ($priceInfo && $priceInfo['has_price']) {
                    $priceType = $priceInfo['price_type'];
                    $priceDetail = '';
                    
                    if ($priceType === 'exact') {
                        $priceDetail = "giá khoảng " . number_format($priceInfo['exact_price']) . "đ";
                    } elseif ($priceType === 'range') {
                        $priceDetail = "giá từ " . number_format($priceInfo['min_price']) . "đ đến " . number_format($priceInfo['max_price']) . "đ";
                    } elseif ($priceType === 'min') {
                        $priceDetail = "giá từ " . number_format($priceInfo['min_price']) . "đ trở lên";
                    } elseif ($priceType === 'max') {
                        $priceDetail = "giá dưới " . number_format($priceInfo['max_price']) . "đ";
                    }
                    
                    $expertPrompt .= " Khách hàng đang tìm sản phẩm với $priceDetail.";
                }
                
                if (!$foundProducts) {
                    $expertPrompt .= "\nKHÔNG tìm thấy sản phẩm nào phù hợp với từ khóa này trong cơ sở dữ liệu của chúng tôi.";
                    
                    // Gợi ý chung chung hơn
                    $expertPrompt .= "\nHãy trả lời: \"Xin lỗi, tôi chưa tìm thấy sản phẩm nào khớp hoàn toàn với \"$searchKeywords\"";
                    if ($priceInfo && $priceInfo['has_price']) {
                         $expertPrompt .= " trong khoảng giá bạn yêu cầu";
                    }
                    $expertPrompt .= ". Bạn có muốn thử tìm với từ khóa khác hoặc xem một số sản phẩm tương tự không?\"";

                } else { // Đã tìm thấy sản phẩm ($foundProducts = true)
                    $expertPrompt .= "\nĐã tìm thấy $productCount sản phẩm thuộc " . count($categoriesFound) . " danh mục: " . implode(', ', $categoriesFound) . ".";
                    
                    if ($priceInfo && $priceInfo['has_price']) {
                        if ($hasProductsInPriceRange) {
                            // Tìm thấy sản phẩm VÀ có sản phẩm trong khoảng giá yêu cầu
                            $expertPrompt .= "\nCó sản phẩm phù hợp với mức giá yêu cầu.";
                            $expertPrompt .= "\nHãy trả lời: \"Tôi đã tìm thấy một số sản phẩm \"$searchKeywords\" phù hợp với yêu cầu";
                            if ($spaceKey) {
                                $spaceName = $this->getSpaceNameFromKey($spaceKey);
                                if ($spaceName !== $spaceKey) $expertPrompt .= " cho $spaceName";
                            }
                             $expertPrompt .= " và mức giá của bạn. Bạn xem các sản phẩm bên dưới nhé.\"";
                        } else {
                             // Tìm thấy sản phẩm NHƯNG KHÔNG có sản phẩm nào trong khoảng giá yêu cầu
                             $expertPrompt .= "\nKHÔNG có sản phẩm nào trong khoảng giá yêu cầu.";
                             $expertPrompt .= "\nHãy trả lời: \"Tôi tìm thấy một số sản phẩm \"$searchKeywords\"";
                              if ($spaceKey) {
                                 $spaceName = $this->getSpaceNameFromKey($spaceKey);
                                 if ($spaceName !== $spaceKey) $expertPrompt .= " cho $spaceName";
                             }
                              $expertPrompt .= ", nhưng tiếc là không có sản phẩm nào khớp với mức giá bạn đưa ra. Bạn có thể tham khảo các sản phẩm này hoặc thử tìm với mức giá khác xem sao.\"";
                        }
                    } else { 
                        // Tìm thấy sản phẩm và KHÔNG có yêu cầu về giá
                         $expertPrompt .= "\nHãy trả lời: \"Tôi đã tìm thấy một số sản phẩm \"$searchKeywords\" phù hợp";
                          if ($spaceKey) {
                             $spaceName = $this->getSpaceNameFromKey($spaceKey);
                             if ($spaceName !== $spaceKey) $expertPrompt .= " cho $spaceName";
                         }
                         $expertPrompt .= ". Bạn xem các sản phẩm bên dưới nhé.\"";
                    }
                    
                     $expertPrompt .= "\n\nTrả lời NGẮN GỌN và TRỰC TIẾP (1-2 câu). KHÔNG giải thích dài dòng. KHÔNG đề xuất sản phẩm ngoài kết quả.";
                }
            } else {
                 // Xử lý trường hợp không phải tìm kiếm sản phẩm
                 $expertPrompt .= "\n\nHãy trả lời câu hỏi của khách hàng: \"" . $message . "\" một cách ngắn gọn (1-2 câu), thân thiện và chuyên nghiệp theo vai trò chuyên gia nội thất.";
            }


            // Chuẩn bị dữ liệu gửi đến API
            $data = [
                'contents' => [
                    [ 'role' => 'user', 'parts' => [ [ 'text' => $expertPrompt ] ] ]
                 ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 150, // Giảm nhẹ để đảm bảo ngắn gọn
                ]
            ];

            // Gọi API với phương thức POST và truyền API key qua query parameter
            $url = $endpoint . '?key=' . $apiKey;

            $response = Http::timeout(30)->withHeaders([ // Thêm timeout
                'Content-Type' => 'application/json',
            ])->post($url, $data);

            // Kiểm tra và xử lý phản hồi
            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Gemini API Response', ['response' => $responseData]);

                // Trích xuất phản hồi từ Gemini (cần kiểm tra cấu trúc response mới của v1.5)
                 if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                    // Xóa các dấu * hoặc markdown dư thừa nếu Gemini trả về
                    $responseText = $responseData['candidates'][0]['content']['parts'][0]['text'];
                    return trim(str_replace(['*', '#'], '', $responseText)); 
                } else {
                    Log::warning('Gemini API response format unexpected', ['response' => $responseData]);
                    return 'Xin lỗi, tôi không thể xử lý yêu cầu của bạn lúc này. (Lỗi định dạng phản hồi)';
                }
            } else {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                 return "Xin lỗi, tôi đang gặp chút sự cố khi kết nối đến bộ não AI. Bạn vui lòng thử lại sau nhé.";
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
             Log::error('Gemini API Connection Error: ' . $e->getMessage());
             return "Xin lỗi, tôi không thể kết nối đến máy chủ AI ngay lúc này. Bạn vui lòng thử lại sau.";
        } catch (\Exception $e) {
            Log::error('Exception when calling Gemini API: ' . $e->getMessage());
             // Không throw $e để tránh lộ thông tin lỗi chi tiết ra ngoài
            return "Đã có lỗi xảy ra trong quá trình xử lý yêu cầu với AI. Vui lòng thử lại sau.";
        }
    }


     /**
     * Chuyển đổi space_key sang tên hiển thị tiếng Việt
     * 
     * @param string $spaceKey
     * @return string|null Tên không gian tiếng Việt hoặc chính key nếu không tìm thấy
     */
    private function getSpaceNameFromKey($spaceKey)
    {
        // Đảo ngược mapping gốc để lấy tên tiếng Việt từ key
        $keyToVnMap = array_flip($this->spaceKeyMapping); // Đảo ngược map gốc
        
        // Bổ sung các key còn thiếu nếu cần (đảm bảo tính đầy đủ)
        $keyToVnMap = $keyToVnMap + [
             'kitchen' => 'phòng bếp', 
             'office' => 'văn phòng', 
             'outdoor' => 'ngoài trời',
             'bathroom' => 'phòng tắm' 
        ];
        
        return $keyToVnMap[$spaceKey] ?? $spaceKey; // Trả về tên TV hoặc chính key nếu không tìm thấy
    }

}
