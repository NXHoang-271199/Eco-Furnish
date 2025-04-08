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
        
        // Phát hiện giá từ tin nhắn
        $priceInfo = $this->extractPriceInfo($message);
        
        // Tìm tất cả danh mục sản phẩm trong tin nhắn
        $foundCategories = [];
        foreach ($categories as $category => $id) {
            if (mb_strpos($message, $category) !== false) {
                $foundCategories[] = $category;
            }
        }

        // Tìm không gian trong tin nhắn - phiên bản cải tiến
        $foundSpace = null;
        $foundSpaceKey = null;
        
        // Trước tiên tìm từ khớp chính xác với map gốc
        foreach ($this->spaceKeyMapping as $spaceText => $spaceKey) {
            if (mb_strpos($message, $spaceText) !== false) {
                $foundSpace = $spaceText;
                $foundSpaceKey = $spaceKey;
                break;
            }
        }
        
        // Nếu không tìm thấy, thử tìm trong từ vựng mở rộng
        if (!$foundSpace) {
            foreach ($this->extendedSpaceVocab as $spaceKey => $spaceTerms) {
                foreach ($spaceTerms as $term) {
                    if (mb_strpos($message, $term) !== false) {
                        // Lấy tên hiển thị từ ánh xạ gốc (đảo ngược)
                        $foundSpaceKey = $spaceKey;
                        $foundSpace = array_search($spaceKey, $this->spaceKeyMapping);
                        break 2; // Thoát cả 2 vòng lặp
                    }
                }
            }
        }

        // Tìm phong cách trong tin nhắn
        $foundStyle = null;
        foreach ($styles as $style) {
            if (mb_strpos($message, $style) !== false) {
                $foundStyle = $style;
                break;
            }
        }

        // Tìm chất liệu trong tin nhắn
        $foundMaterial = null;
        foreach ($materials as $material) {
            if (mb_strpos($message, $material) !== false) {
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
            $keywords[] = $foundSpace;
        }
        
        if ($foundStyle) {
            $keywords[] = $foundStyle;
        }
        
        if ($foundMaterial) {
            $keywords[] = $foundMaterial;
        }

        // Nếu không tìm thấy thông tin cụ thể, sử dụng toàn bộ tin nhắn sau khi loại bỏ stopwords
        if (empty($keywords)) {
            // Loại bỏ các stopwords
            foreach ($stopWords as $word) {
                $message = str_replace(' ' . $word . ' ', ' ', ' ' . $message . ' ');
            }
            
            $message = trim($message);
            
            // Nếu tin nhắn quá ngắn sau khi loại bỏ stopwords, sử dụng tin nhắn gốc
            if (mb_strlen($message) < 3) {
                return [
                    'keywords' => $message,
                    'price_info' => $priceInfo,
                    'space_key' => $foundSpaceKey
                ];
            }
            
            return [
                'keywords' => $message,
                'price_info' => $priceInfo,
                'space_key' => $foundSpaceKey
            ];
        }
        
        // Kết hợp các từ khóa tìm được
        return [
            'keywords' => implode(' ', $keywords),
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
        // Ví dụ: "dưới 1 triệu", "không quá 500k", "tối đa 2tr"
        $maxPricePatterns = [
            // "dưới X"
            '/(?:dưới|không quá|tối đa|chỉ|<=|<)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "dưới mức X"
            '/(?:dưới|không quá|tối đa|chỉ)\s+(?:mức|giá)\s+(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu',
            // "X trở xuống"
            '/(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)\s+trở xuống/iu'
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
            '/(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)\s+trở lên/iu'
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
            '/(\d+[k\.]?\d*)\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)\b/iu'
        ];
        
        foreach ($exactPricePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $priceText = $matches[1];
                Log::info("Phát hiện giá chính xác: '{$priceText}'");
                
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
        
        // 5. Tìm số tiền đơn lẻ trong tin nhắn (ưu tiên thấp nhất)
        // Ví dụ: "500k", "1 triệu", "2tr" - chỉ khi tin nhắn có hỏi về giá
        if (mb_strpos($message, 'giá') !== false || mb_strpos($message, 'bao nhiêu') !== false) {
            $moneyPattern = '/\b(\d+[k\.]?\d*)\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?\b/iu';
            
            if (preg_match($moneyPattern, $message, $matches)) {
                $priceText = $matches[1];
                Log::info("Phát hiện số tiền: '{$priceText}'");
                
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
            // Điều kiện: keywords có nhiều hơn 2 từ và không chứa các từ khóa không gian/danh mục chung
            $isProductNameSearch = false;
            $potentialProductName = trim($keywords); // Giả sử ban đầu keywords là tên sản phẩm
            $commonSearchTerms = ['phòng', 'không gian', 'khu vực', 'nơi', 'bàn', 'ghế', 'tủ', 'giường', 'kệ', 'đèn', 'thảm', 'gương', 'sofa', 'giá'];
            
            // Nếu keywords dài và không chứa các từ khóa chung, có thể là tên sản phẩm
            if (count($keywordParts) > 1) {
                $isProductNameSearch = true;
                foreach ($commonSearchTerms as $term) {
                    if (mb_strpos(mb_strtolower($potentialProductName, 'UTF-8'), $term) !== false) {
                        $isProductNameSearch = false;
                        break;
                    }
                }
                // Kiểm tra xem có chứa từ khóa không gian không
                foreach (array_keys($this->spaceKeyMapping) as $spaceText) {
                    if (mb_strpos(mb_strtolower($potentialProductName, 'UTF-8'), $spaceText) !== false) {
                        $isProductNameSearch = false;
                        break;
                    }
                }
            }


            // Ưu tiên tìm kiếm theo tên sản phẩm nếu được xác định
            if ($isProductNameSearch) {
                Log::info('Ưu tiên tìm kiếm theo tên sản phẩm: ' . $potentialProductName);
                $query->where(function($q) use ($potentialProductName) {
                    // Tìm kiếm chính xác hoặc gần đúng trong tên sản phẩm
                    $q->where('name', 'like', '%' . $potentialProductName . '%');
                    // Có thể thêm tìm kiếm full-text hoặc dùng soundex/metaphone nếu cần độ chính xác cao hơn
                });
            } else {
                Log::info('Thực hiện tìm kiếm theo từ khóa chung, không gian và danh mục.');
                // Nếu không phải tìm kiếm theo tên, tiếp tục logic cũ
            
            // Nếu có space_key, lọc sản phẩm theo danh mục thuộc không gian đó
            if ($spaceKey) {
                // Lấy danh sách category_id thuộc space này
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
            
            // Nếu có space_key nhưng không có không gian được phát hiện trong từ khóa,
            // thêm không gian tương ứng vào danh sách không gian đã phát hiện
            if ($spaceKey && empty($detectedSpaces)) {
                $spaceName = $this->getSpaceNameFromKey($spaceKey);
                    if ($spaceName && $spaceName !== $spaceKey) { // Chỉ thêm nếu tên tiếng Việt tồn tại và khác key
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
            
            // Nếu có space_key, coi như đang tìm kiếm theo không gian
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
                
                // Thêm tất cả các sản phẩm liên quan đến không gian đã phát hiện
                foreach ($detectedSpaces as $space) {
                    if (isset($spaceToProductMapping[$space])) {
                        $relatedProductTerms = array_merge($relatedProductTerms, $spaceToProductMapping[$space]);
                        
                        // Thêm từ khóa không gian tiếng Anh để tìm kiếm rộng hơn
                        if (isset($spaceMapping[$space])) {
                            $keywordParts = array_merge($keywordParts, $spaceMapping[$space]);
                        }
                    }
                }
                
                // Nếu không tìm thấy không gian cụ thể nhưng từ khóa chứa 'phòng',
                // thử tìm kiếm thông qua các từ khóa đi kèm
                if (empty($detectedSpaces) && $isSpaceSearch && !$spaceKey) {
                    // Thử tìm các từ gợi ý không gian khác
                    $possibleSpaceHints = [];
                    
                    // Các từ khóa phụ thường đi kèm với các loại phòng
                    $spaceHints = [
                        'khách' => 'phòng khách',
                        'ngủ' => 'phòng ngủ',
                        'ăn' => 'phòng ăn',
                        'làm việc' => 'phòng làm việc',
                        'tắm' => 'phòng tắm',
                            'bếp' => 'phòng ăn', // Bếp thường liên quan phòng ăn
                        'nấu ăn' => 'phòng ăn',
                        'tiếp khách' => 'phòng khách'
                    ];
                    
                    foreach ($spaceHints as $hint => $space) {
                        if (mb_strpos(mb_strtolower($keywords, 'UTF-8'), $hint) !== false) {
                            $possibleSpaceHints[] = $space;
                        }
                    }
                    
                    // Thêm các sản phẩm liên quan nếu tìm thấy gợi ý không gian
                        foreach (array_unique($possibleSpaceHints) as $space) {
                        if (isset($spaceToProductMapping[$space])) {
                            $relatedProductTerms = array_merge($relatedProductTerms, $spaceToProductMapping[$space]);
                            
                            // Thêm từ khóa không gian tiếng Anh để tìm kiếm rộng hơn
                            if (isset($spaceMapping[$space])) {
                                $keywordParts = array_merge($keywordParts, $spaceMapping[$space]);
                            }
                        }
                    }
                    
                        // Nếu vẫn không tìm thấy, mặc định là phòng khách nếu có từ 'phòng'
                    if (empty($possibleSpaceHints) && mb_strpos(mb_strtolower($keywords, 'UTF-8'), 'phòng') !== false) {
                            Log::info('Không xác định được loại phòng cụ thể, mặc định là phòng khách');
                            $detectedSpaces[] = 'phòng khách'; // Thêm vào detectedSpaces để xử lý ở dưới
                        $relatedProductTerms = array_merge($relatedProductTerms, $spaceToProductMapping['phòng khách']);
                        $keywordParts = array_merge($keywordParts, $spaceMapping['phòng khách']);
                    }
                }
                
                    $relatedProductTerms = array_unique($relatedProductTerms); // Loại bỏ trùng lặp
                    Log::info('Mở rộng tìm kiếm sang các sản phẩm liên quan đến không gian: ' . implode(', ', $relatedProductTerms));
                
                    // Thêm các sản phẩm liên quan vào danh sách tìm kiếm hoặc lọc theo danh mục
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
                        // Nếu không phải term của category, thêm vào keywordParts để tìm kiếm rộng hơn
                        if (!$isCategoryTerm) {
                    $keywordParts[] = $term;
                }
                    }
                    $keywordParts = array_unique($keywordParts); // Loại bỏ trùng lặp
            }
            
                // Tìm tất cả danh mục sản phẩm trong từ khóa ban đầu (nếu chưa được thêm từ logic không gian)
            foreach ($productCategories as $category => $terms) {
                foreach ($terms as $term) {
                    if (mb_strpos(mb_strtolower($keywords, 'UTF-8'), $term) !== false) {
                        if (!in_array($category, $specificCategories)) {
                            $specificCategories[] = $category;
                        }
                    }
                }
            }
            
                $specificCategories = array_unique($specificCategories); // Loại bỏ trùng lặp

                // Xây dựng điều kiện WHERE phức tạp
                $query->where(function($mainQuery) use ($specificCategories, $productCategories, $keywordParts, $keywords) {
                    // 1. Lọc theo danh mục cụ thể nếu có
            if (!empty($specificCategories)) {
                        Log::info('Lọc theo danh mục cụ thể: ' . implode(', ', $specificCategories));
                        $mainQuery->whereHas('category', function($q) use ($specificCategories, $productCategories) {
                            $q->where(function($subQ) use ($specificCategories, $productCategories) {
                    foreach ($specificCategories as $category) {
                                    $categoryTerms = $productCategories[$category] ?? [$category]; // Lấy terms hoặc dùng chính category name
                                foreach ($categoryTerms as $term) {
                                    $subQ->orWhere('name', 'like', '%' . $term . '%');
                                    }
                                }
                            });
                        });
                    }

                    // 2. Tìm kiếm theo các từ khóa còn lại trong tên, mô tả, hoặc tên danh mục (nếu không có danh mục cụ thể)
                     // Chỉ thực hiện tìm kiếm keyword chung nếu không có danh mục cụ thể HOẶC nếu có danh mục nhưng vẫn muốn tìm keyword khác
                    if (empty($specificCategories) || count($keywordParts) > 1) {
                         Log::info('Tìm kiếm theo các từ khóa: ' . implode(', ', $keywordParts));
                         $mainQuery->orWhere(function ($q) use ($keywordParts) {
                        foreach ($keywordParts as $part) {
                                 $part = trim($part);
                                 if (mb_strlen($part) >= 2) { // Chỉ tìm với từ có độ dài >= 2
                                $q->orWhere('name', 'like', '%' . $part . '%')
                                  ->orWhere('description', 'like', '%' . $part . '%')
                                  ->orWhereHas('category', function($categoryQuery) use ($part) {
                                      $categoryQuery->where('name', 'like', '%' . $part . '%');
                                  });
                            }
                        }
                    });
                    } elseif (!empty($specificCategories) && count($keywordParts) <= 1 && mb_strlen(trim($keywords)) >= 2) {
                         // Trường hợp chỉ có danh mục cụ thể và từ khóa gốc đủ dài -> tìm kiếm thêm từ khóa gốc trong tên/mô tả
                         Log::info('Tìm kiếm thêm từ khóa gốc "' . $keywords . '" trong tên/mô tả cho danh mục đã lọc.');
                         $mainQuery->orWhere(function ($q) use ($keywords) {
                        $q->where('name', 'like', '%' . $keywords . '%')
                              ->orWhere('description', 'like', '%' . $keywords . '%');
                    });
                }
                });
            } // Kết thúc else của isProductNameSearch
            
            // Áp dụng bộ lọc giá (giữ nguyên logic lọc giá)
            if ($priceInfo && $priceInfo['has_price']) {
                Log::info('Đang áp dụng bộ lọc giá...');
                
                // Tạo một bản sao của query để kiểm tra sản phẩm có trong khoảng giá không
                $queryWithPriceFilter = clone $query;
                
                // Xử lý giá chính xác
                if ($priceInfo['price_type'] === 'exact' && $priceInfo['exact_price'] !== null) {
                    $exactPrice = $priceInfo['exact_price'];
                    $minPrice = $exactPrice * 0.85; 
                    $maxPrice = $exactPrice * 1.15; 
                    Log::info("Lọc sản phẩm với giá chính xác: {$exactPrice} VND, phạm vi [{$minPrice} - {$maxPrice}] VND");
                    $queryWithPriceFilter->where(function($q) use ($minPrice, $maxPrice) {
                         $q->where(function($subQ) use ($minPrice, $maxPrice) { // Check product price
                                $subQ->whereBetween('price', [$minPrice, $maxPrice])
                                    ->orWhere(function($priceQ) use ($minPrice, $maxPrice) {
                                        $priceQ->whereBetween('discount_price', [$minPrice, $maxPrice])
                                            ->whereNotNull('discount_price');
                                    });
                         })->orWhereHas('variants', function($variantQuery) use ($minPrice, $maxPrice) { // Check variant price
                              $variantQuery->where(function($subQ) use ($minPrice, $maxPrice) {
                            $subQ->whereBetween('price', [$minPrice, $maxPrice])
                                ->orWhere(function($priceQ) use ($minPrice, $maxPrice) {
                                    $priceQ->whereBetween('discount_price', [$minPrice, $maxPrice])
                                        ->whereNotNull('discount_price');
                                      });
                                });
                        });
                    });
                }
                
                // Xử lý phạm vi giá
                if ($priceInfo['price_type'] === 'range' && $priceInfo['min_price'] !== null && $priceInfo['max_price'] !== null) {
                    $minPrice = $priceInfo['min_price'];
                    $maxPrice = $priceInfo['max_price'];
                    Log::info("Lọc sản phẩm với phạm vi giá: [{$minPrice} - {$maxPrice}] VND");
                     $queryWithPriceFilter->where(function($q) use ($minPrice, $maxPrice) {
                         $q->where(function($subQ) use ($minPrice, $maxPrice) { // Check product price
                                $subQ->whereBetween('price', [$minPrice, $maxPrice])
                                    ->orWhere(function($priceQ) use ($minPrice, $maxPrice) {
                                        $priceQ->whereBetween('discount_price', [$minPrice, $maxPrice])
                                            ->whereNotNull('discount_price');
                                    });
                         })->orWhereHas('variants', function($variantQuery) use ($minPrice, $maxPrice) { // Check variant price
                              $variantQuery->where(function($subQ) use ($minPrice, $maxPrice) {
                            $subQ->whereBetween('price', [$minPrice, $maxPrice])
                                ->orWhere(function($priceQ) use ($minPrice, $maxPrice) {
                                    $priceQ->whereBetween('discount_price', [$minPrice, $maxPrice])
                                        ->whereNotNull('discount_price');
                                      });
                                });
                        });
                    });
                }
                
                // Xử lý giá tối đa
                if ($priceInfo['price_type'] === 'max' && $priceInfo['max_price'] !== null) {
                    $maxPrice = $priceInfo['max_price'];
                    Log::info("Lọc sản phẩm với giá tối đa: {$maxPrice} VND");
                     $queryWithPriceFilter->where(function($q) use ($maxPrice) {
                         $q->where(function($subQ) use ($maxPrice) { // Check product price
                                $subQ->where('price', '<=', $maxPrice)
                                    ->orWhere(function($priceQ) use ($maxPrice) {
                                        $priceQ->where('discount_price', '<=', $maxPrice)
                                            ->whereNotNull('discount_price');
                                    });
                         })->orWhereHas('variants', function($variantQuery) use ($maxPrice) { // Check variant price
                              $variantQuery->where(function($subQ) use ($maxPrice) {
                            $subQ->where('price', '<=', $maxPrice)
                                ->orWhere(function($priceQ) use ($maxPrice) {
                                    $priceQ->where('discount_price', '<=', $maxPrice)
                                        ->whereNotNull('discount_price');
                                      });
                                });
                        });
                    });
                }
                
                // Xử lý giá tối thiểu
                if ($priceInfo['price_type'] === 'min' && $priceInfo['min_price'] !== null) {
                    $minPrice = $priceInfo['min_price'];
                    Log::info("Lọc sản phẩm với giá tối thiểu: {$minPrice} VND");
                    $queryWithPriceFilter->where(function($q) use ($minPrice) {
                        $q->where(function($subQ) use ($minPrice) { // Check product price
                                $subQ->where('price', '>=', $minPrice)
                                    ->orWhere(function($priceQ) use ($minPrice) {
                                        $priceQ->where('discount_price', '>=', $minPrice)
                                            ->whereNotNull('discount_price');
                                    });
                         })->orWhereHas('variants', function($variantQuery) use ($minPrice) { // Check variant price
                              $variantQuery->where(function($subQ) use ($minPrice) {
                            $subQ->where('price', '>=', $minPrice)
                                ->orWhere(function($priceQ) use ($minPrice) {
                                    $priceQ->where('discount_price', '>=', $minPrice)
                                        ->whereNotNull('discount_price');
                                      });
                                });
                        });
                    });
                }
                 // Gán lại query đã lọc giá
                 $query = $queryWithPriceFilter;
            }

            // Lấy kết quả
            $products = $query->orderBy('created_at', 'desc')
                ->limit(15) // Giới hạn số lượng kết quả
                ->get();

            Log::info('Tìm thấy ' . $products->count() . ' sản phẩm sau khi lọc.');

            // Kiểm tra xem có sản phẩm nào phù hợp với khoảng giá yêu cầu không (dùng query gốc trước khi lọc giá)
            $hasProductsInPriceRange = false;
            if ($priceInfo && $priceInfo['has_price']) {
                 // Tạo lại query gốc (không lọc giá) để kiểm tra
                 $originalQuery = Product::query(); // Bắt đầu lại query gốc
                  // Áp dụng lại các điều kiện lọc ban đầu (tên, keyword, space, category)
                 if ($isProductNameSearch) {
                     $originalQuery->where('name', 'like', '%' . $potentialProductName . '%');
                 } else {
                     // Áp dụng lại logic lọc không gian nếu có
                     if ($spaceKey) {
                         $categoryIdsInSpace = DB::table('category_space')
                                                ->where('space_key', $spaceKey)
                                                ->pluck('category_id')
                                                ->unique()->toArray();
                         if (!empty($categoryIdsInSpace)) {
                             $originalQuery->whereIn('category_id', $categoryIdsInSpace);
                         }
                     }
                     // Áp dụng lại logic lọc keyword/category phức tạp
                      $originalQuery->where(function($mainQuery) use ($specificCategories, $productCategories, $keywordParts, $keywords) {
                            if (!empty($specificCategories)) {
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
                            if (empty($specificCategories) || count($keywordParts) > 1) {
                                $mainQuery->orWhere(function ($q) use ($keywordParts) {
                                    foreach ($keywordParts as $part) {
                                        $part = trim($part);
                                        if (mb_strlen($part) >= 2) {
                                            $q->orWhere('name', 'like', '%' . $part . '%')
                                            ->orWhere('description', 'like', '%' . $part . '%')
                                            ->orWhereHas('category', function($categoryQuery) use ($part) {
                                                $categoryQuery->where('name', 'like', '%' . $part . '%');
                                            });
                                        }
                                    }
                                });
                            } elseif (!empty($specificCategories) && count($keywordParts) <= 1 && mb_strlen(trim($keywords)) >= 2) {
                                $mainQuery->orWhere(function ($q) use ($keywords) {
                                    $q->where('name', 'like', '%' . $keywords . '%')
                                    ->orWhere('description', 'like', '%' . $keywords . '%');
                                });
                            }
                        });
                 }

                 // Bây giờ kiểm tra xem có sản phẩm nào trong query gốc này khớp với điều kiện giá không
                  $priceCheckQuery = clone $originalQuery; // Clone query gốc đã lọc keyword/space/cat
                  
                   // Áp dụng điều kiện giá vào query clone này
                   if ($priceInfo['price_type'] === 'exact') {
                       $exactPrice = $priceInfo['exact_price']; $minPrice = $exactPrice * 0.85; $maxPrice = $exactPrice * 1.15;
                       $priceCheckQuery->where(function($q) use ($minPrice, $maxPrice) { /* logic giá exact */
                             $q->where(function($subQ) use ($minPrice, $maxPrice) { $subQ->whereBetween('price', [$minPrice, $maxPrice])->orWhere(function($priceQ) use ($minPrice, $maxPrice) { $priceQ->whereBetween('discount_price', [$minPrice, $maxPrice])->whereNotNull('discount_price'); }); })
                               ->orWhereHas('variants', function($variantQuery) use ($minPrice, $maxPrice) { $variantQuery->where(function($subQ) use ($minPrice, $maxPrice) { $subQ->whereBetween('price', [$minPrice, $maxPrice])->orWhere(function($priceQ) use ($minPrice, $maxPrice) { $priceQ->whereBetween('discount_price', [$minPrice, $maxPrice])->whereNotNull('discount_price'); }); }); });
                       });
                   } elseif ($priceInfo['price_type'] === 'range') {
                       $minPrice = $priceInfo['min_price']; $maxPrice = $priceInfo['max_price'];
                       $priceCheckQuery->where(function($q) use ($minPrice, $maxPrice) { /* logic giá range */
                            $q->where(function($subQ) use ($minPrice, $maxPrice) { $subQ->whereBetween('price', [$minPrice, $maxPrice])->orWhere(function($priceQ) use ($minPrice, $maxPrice) { $priceQ->whereBetween('discount_price', [$minPrice, $maxPrice])->whereNotNull('discount_price'); }); })
                               ->orWhereHas('variants', function($variantQuery) use ($minPrice, $maxPrice) { $variantQuery->where(function($subQ) use ($minPrice, $maxPrice) { $subQ->whereBetween('price', [$minPrice, $maxPrice])->orWhere(function($priceQ) use ($minPrice, $maxPrice) { $priceQ->whereBetween('discount_price', [$minPrice, $maxPrice])->whereNotNull('discount_price'); }); }); });
                       });
                   } elseif ($priceInfo['price_type'] === 'min') {
                       $minPrice = $priceInfo['min_price'];
                       $priceCheckQuery->where(function($q) use ($minPrice) { /* logic giá min */
                            $q->where(function($subQ) use ($minPrice) { $subQ->where('price', '>=', $minPrice)->orWhere(function($priceQ) use ($minPrice) { $priceQ->where('discount_price', '>=', $minPrice)->whereNotNull('discount_price'); }); })
                               ->orWhereHas('variants', function($variantQuery) use ($minPrice) { $variantQuery->where(function($subQ) use ($minPrice) { $subQ->where('price', '>=', $minPrice)->orWhere(function($priceQ) use ($minPrice) { $priceQ->where('discount_price', '>=', $minPrice)->whereNotNull('discount_price'); }); }); });
                       });
                   } elseif ($priceInfo['price_type'] === 'max') {
                       $maxPrice = $priceInfo['max_price'];
                       $priceCheckQuery->where(function($q) use ($maxPrice) { /* logic giá max */
                           $q->where(function($subQ) use ($maxPrice) { $subQ->where('price', '<=', $maxPrice)->orWhere(function($priceQ) use ($maxPrice) { $priceQ->where('discount_price', '<=', $maxPrice)->whereNotNull('discount_price'); }); })
                               ->orWhereHas('variants', function($variantQuery) use ($maxPrice) { $variantQuery->where(function($subQ) use ($maxPrice) { $subQ->where('price', '<=', $maxPrice)->orWhere(function($priceQ) use ($maxPrice) { $priceQ->where('discount_price', '<=', $maxPrice)->whereNotNull('discount_price'); }); }); });
                       });
                   }
                   
                   $hasProductsInPriceRange = $priceCheckQuery->exists(); // Kiểm tra xem có bản ghi nào khớp không
                   Log::info('Kiểm tra sản phẩm trong khoảng giá yêu cầu (trước khi trả về kết quả): ' . ($hasProductsInPriceRange ? 'Có' : 'Không'));

            } else {
                 // Nếu không có yêu cầu giá, mặc định là true nếu có sản phẩm tìm thấy
                 $hasProductsInPriceRange = $products->isNotEmpty();
            }


            // Phân loại sản phẩm theo danh mục và định dạng lại
            $categorizedProducts = [];
            $foundCategories = [];
            $productsInPriceRange = []; // Mảng lưu các sản phẩm thực sự trả về (đã lọc giá)
            
            $formattedProducts = $products->map(function ($product) use (&$categorizedProducts, &$foundCategories, &$productsInPriceRange, $priceInfo) {
                // ... (Giữ nguyên logic định dạng sản phẩm từ dòng 968 đến 1072) ...
                // Đảm bảo có đường dẫn hình ảnh đúng
                $imagePath = null;
                
                if (!empty($product->image_thumnail)) {
                    $imagePath = $product->image_thumnail;
                }
                
                $categoryName = $product->category ? $product->category->name : 'N/A';
                
                // Thêm thông tin phân loại
                if (!in_array($categoryName, $foundCategories)) {
                    $foundCategories[] = $categoryName;
                }
                
                // Xác định giá hiển thị dựa trên việc sản phẩm có biến thể hay không
                $displayPrice = 0;
                $productPrice = 0;
                $productDiscountPrice = null;
                $hasVariants = $product->variants->isNotEmpty();
                
                if ($hasVariants) {
                    // Nếu có biến thể, lấy biến thể có giá thấp nhất
                    $lowestPriceVariant = $product->variants->sortBy(function($variant) {
                        return $variant->discount_price ?? $variant->price;
                    })->first();
                    
                    if ($lowestPriceVariant) {
                        $productPrice = $lowestPriceVariant->price;
                        $productDiscountPrice = $lowestPriceVariant->discount_price;
                        $displayPrice = $productDiscountPrice ?? $productPrice;
                    }
                } else {
                    // Nếu không có biến thể, sử dụng giá của sản phẩm
                    $productPrice = $product->price;
                    $productDiscountPrice = $product->discount_price;
                    $displayPrice = $productDiscountPrice ?? $productPrice;
                }
                
                 // Biến này chỉ để kiểm tra logic nội bộ, không dùng để lọc nữa vì đã lọc bằng query
                 $productInPriceRangeCheck = true; // Mặc định là true vì đã lọc bằng query
                
                // Đưa danh mục vào metadata sản phẩm
                $formattedProduct = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $productPrice,
                    'discount_price' => $productDiscountPrice,
                    'image' => $imagePath,
                    'category' => $categoryName,
                     'category_group' => $categoryName, // Giữ lại để tương thích nếu FE đang dùng
                    'description' => Str::limit($product->description, 100),
                     // 'in_price_range' => $productInPriceRangeCheck, // Không cần trường này nữa
                    'display_price' => $displayPrice,
                    'has_variants' => $hasVariants,
                    'variant_count' => $product->variants->count(),
                    'variants' => $hasVariants ? $product->variants->map(function($variant) {
                        return [
                            'id' => $variant->id,
                            'price' => $variant->price,
                            'discount_price' => $variant->discount_price,
                             'variant_details' => $variant->variant_details // Giả sử có trường này
                        ];
                     })->toArray() : []
                ];
                
                    // Phân loại sản phẩm vào nhóm danh mục
                    if (!isset($categorizedProducts[$categoryName])) {
                        $categorizedProducts[$categoryName] = [];
                    }
                    $categorizedProducts[$categoryName][] = $formattedProduct;
                    
                // Thêm vào mảng sản phẩm cuối cùng
                    $productsInPriceRange[] = $formattedProduct;
                    
                return $formattedProduct; // Trả về sản phẩm đã định dạng
            })->values(); // Chuẩn hóa lại index của mảng

            // Sắp xếp sản phẩm dựa vào yêu cầu giá (nếu có)
            $sortedProducts = $productsInPriceRange; // Lấy từ mảng đã lọc giá bằng query
            
            if ($priceInfo && $priceInfo['has_price']) {
                // Nếu tìm với giá tối thiểu (min), sắp xếp từ cao đến thấp
                if ($priceInfo['price_type'] === 'min') {
                    usort($sortedProducts, function($a, $b) {
                        return $b['display_price'] - $a['display_price'];
                    });
                } 
                // Nếu tìm với giá tối đa (max) hoặc phạm vi, hoặc giá chính xác, sắp xếp từ thấp đến cao
                else if (in_array($priceInfo['price_type'], ['max', 'range', 'exact'])) {
                    usort($sortedProducts, function($a, $b) {
                        return $a['display_price'] - $b['display_price'];
                    });
                }
            }
            
            Log::info('Số sản phẩm cuối cùng trả về: ' . count($sortedProducts));
            
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
                'products' => $sortedProducts, // Trả về danh sách đã lọc giá và sắp xếp
                'categories' => array_values(array_unique($foundCategories)), // Lấy danh mục từ các sản phẩm tìm thấy
                'categorized_products' => $sortedCategorizedProducts, 
                'has_products_in_price_range' => $hasProductsInPriceRange // Trạng thái trước khi lọc giá cuối cùng
            ];
        } catch (\Exception $e) {
            Log::error('Error searching products: ' . $e->getMessage() . ' on line ' . $e->getLine());
            // Log thêm stack trace để debug dễ hơn
             Log::error($e->getTraceAsString());
            return [
                'products' => [],
                'categories' => [],
                'categorized_products' => [],
                'has_products_in_price_range' => false
            ];
        }
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
                    $expertPrompt .= " Khách hàng đang tìm sản phẩm cho \"$spaceName\".";
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
                    $expertPrompt .= "\nKHÔNG tìm thấy sản phẩm nào phù hợp với từ khóa này trong cơ sở dữ liệu của chúng tôi.
Hãy bắt đầu câu trả lời của bạn bằng: \"Xin lỗi, hiện tại chúng tôi không có sản phẩm nào phù hợp với yêu cầu tìm kiếm của bạn.\"
Sau đó, bạn có thể đề xuất một số sản phẩm tương tự hoặc gợi ý khách hàng thử tìm kiếm với từ khóa khác.";
                } else {
                    $expertPrompt .= "\nĐã tìm thấy $productCount sản phẩm thuộc " . count($categoriesFound) . " danh mục: " . implode(', ', $categoriesFound) . ".";
                    
                    if ($priceInfo && $priceInfo['has_price']) {
                        if ($hasProductsInPriceRange) {
                            $expertPrompt .= "\nCó sản phẩm phù hợp với mức giá yêu cầu.";
                            $expertPrompt .= "\nHãy trả lời: \"Tôi đã tìm thấy một số sản phẩm phù hợp với yêu cầu của bạn. Bạn có thể xem các sản phẩm bên dưới.\"";
                        } else {
                            $expertPrompt .= "\nKHÔNG có sản phẩm nào trong khoảng giá yêu cầu.";
                            $expertPrompt .= "\nHãy trả lời: \"Tôi đã tìm thấy một số sản phẩm " . $searchKeywords . " nhưng không có sản phẩm nào đúng với mức giá bạn yêu cầu. Bạn có thể xem các sản phẩm dưới đây và cân nhắc mức giá khác.\"";
                        }
                    } else if ($spaceKey) {
                        $spaceName = $this->getSpaceNameFromKey($spaceKey);
                        $expertPrompt .= "\nHãy trả lời: \"Tôi đã tìm thấy một số sản phẩm phù hợp cho $spaceName của bạn. Bạn có thể xem các sản phẩm bên dưới.\"";
                    } else {
                        $expertPrompt .= "\nHãy trả lời: \"Tôi đã tìm thấy một số sản phẩm phù hợp với yêu cầu của bạn. Bạn có thể xem các sản phẩm bên dưới.\"";
                    }
                    
                    $expertPrompt .= "\n\nTrả lời NGẮN GỌN và TRỰC TIẾP. KHÔNG đưa ra giải thích dài dòng về sản phẩm.
KHÔNG đề xuất các sản phẩm ngoài danh sách kết quả tìm kiếm.
Câu trả lời của bạn KHÔNG nên dài quá 1-2 câu.";
                }
            }



            $expertPrompt .= "\n\nCâu hỏi của khách hàng: " . $message;
            
            // Chuẩn bị dữ liệu gửi đến API
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $expertPrompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 250, // Giảm số lượng token tối đa để phản hồi ngắn gọn hơn
                ]
            ];

            // Gọi API với phương thức POST và truyền API key qua query parameter
            $url = $endpoint . '?key=' . $apiKey;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $data);

            // Kiểm tra và xử lý phản hồi
            if ($response->successful()) {
                $responseData = $response->json();

                // Debug response
                Log::info('Gemini API Response', ['response' => $responseData]);

                // Trích xuất phản hồi từ Gemini
                if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                    return $responseData['candidates'][0]['content']['parts'][0]['text'];
                } else {
                    Log::warning('Gemini API response format unexpected', ['response' => $responseData]);
                    return 'Xin lỗi, tôi không thể xử lý yêu cầu của bạn lúc này.';
                }
            } else {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                // Fallback response khi API gặp lỗi
                return "Xin chào! Tôi là trợ lý AI của Eco-Furnish. Rất vui được giúp đỡ bạn. Hiện tại tôi đang gặp một chút vấn đề kỹ thuật. Vui lòng thử lại sau hoặc liên hệ với nhân viên tư vấn của chúng tôi để được hỗ trợ tốt nhất.";
            }
        } catch (\Exception $e) {
            Log::error('Exception when calling Gemini API: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Chuyển đổi space_key sang tên hiển thị tiếng Việt
     * 
     * @param string $spaceKey
     * @return string|null Tên không gian tiếng Việt hoặc null nếu không tìm thấy
     */
    private function getSpaceNameFromKey($spaceKey)
    {
        // Đảo ngược mapping gốc để lấy tên tiếng Việt từ key
        $vnToSpaceKey = array_flip($this->spaceKeyMapping);
        
        // Thêm các key chưa có trong mapping gốc (ví dụ: kitchen)
        $keyToVnMap = $vnToSpaceKey + [
             'kitchen' => 'phòng bếp', // Bổ sung kitchen
             'office' => 'văn phòng', // Đảm bảo office có map
             'outdoor' => 'ngoài trời', // Đảm bảo outdoor có map
             'bathroom' => 'phòng tắm' // Đảm bảo bathroom có map
        ];
        
        return $keyToVnMap[$spaceKey] ?? $spaceKey; // Trả về tên TV hoặc chính key nếu không tìm thấy
    }

}
