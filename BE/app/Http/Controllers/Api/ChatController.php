<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use Illuminate\Support\Str;

class ChatController extends Controller
{
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
                    'has_products_in_price_range' => $hasProductsInPriceRange
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
                    'has_products_in_price_range' => $hasProductsInPriceRange
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
        $spaces = ['phòng khách', 'phòng ngủ', 'phòng ăn', 'phòng làm việc', 'văn phòng', 'nhà bếp', 'phòng tắm'];
        
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

        // Tìm không gian trong tin nhắn
        $foundSpace = null;
        foreach ($spaces as $space) {
            if (mb_strpos($message, $space) !== false) {
                $foundSpace = $space;
                break;
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
                    'price_info' => $priceInfo
                ];
            }

            
            return [
                'keywords' => $message,
                'price_info' => $priceInfo
            ];
        }
        

        // Kết hợp các từ khóa tìm được
        return [
            'keywords' => implode(' ', $keywords),
            'price_info' => $priceInfo
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
        $priceInfo = [
            'has_price' => false,
            'min_price' => null,
            'max_price' => null,
            'exact_price' => null,
            'price_type' => null // 'exact', 'range', 'min', 'max'
        ];
        
        // Tìm phạm vi giá "từ X đến Y" (kiểm tra trước để ưu tiên hơn các mẫu khác)
        // Mẫu: "từ 20k đến 30k" hoặc "từ 20.000 đến 30.000" hoặc "20k-30k"
        $rangePattern = '/(?:từ\s+)?(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)(?:\s*(?:-|đến)\s*)(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu';
        if (preg_match($rangePattern, $message, $matches)) {
            $priceInfo['has_price'] = true;
            $priceInfo['price_type'] = 'range';
            
            $minPriceText = $matches[1];
            $maxPriceText = $matches[2];
            
            Log::info("Phát hiện phạm vi giá: {$minPriceText} đến {$maxPriceText}");
            
            $priceInfo['min_price'] = $this->convertPriceTextToNumber($minPriceText);
            $priceInfo['max_price'] = $this->convertPriceTextToNumber($maxPriceText);
            
            // Mở rộng phạm vi giá thêm ±10% để tăng khả năng khớp
            $priceInfo['min_price'] = $priceInfo['min_price'] * 0.9;
            $priceInfo['max_price'] = $priceInfo['max_price'] * 1.1;
            
            Log::info("Sau khi chuyển đổi: {$priceInfo['min_price']} đến {$priceInfo['max_price']}");
            
            return $priceInfo;
        }
        
        // Tìm mức giá chính xác
        // Mẫu: "giá 100.000" hoặc "100.000 đồng" hoặc "100.000đ" hoặc "100.000 VND"
        $exactPricePattern = '/(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu';
        if (preg_match_all($exactPricePattern, $message, $matches)) {
            $priceInfo['has_price'] = true;
            
            // Lấy giá từ kết quả match
            $priceText = $matches[0][0];
            $price = $this->convertPriceTextToNumber($priceText);
            
            $priceInfo['exact_price'] = $price;
            $priceInfo['price_type'] = 'exact';
            
            // Nếu có từ "khoảng" trước giá, xác định một phạm vi giá
            if (mb_strpos($message, 'khoảng') !== false) {
                $priceInfo['min_price'] = $price * 0.8; // Giảm 20%
                $priceInfo['max_price'] = $price * 1.2; // Tăng 20%
                $priceInfo['price_type'] = 'range';
            }
        }
        
        // Tìm giá tối đa
        // Mẫu: "dưới 200.000" hoặc "không quá 200.000" hoặc "tối đa 200.000"
        $maxPricePattern = '/(dưới|không quá|tối đa|<=|<)(\s)*(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu';
        if (preg_match($maxPricePattern, $message, $matches)) {
            $priceInfo['has_price'] = true;
            $priceInfo['price_type'] = 'max';
            
            // Lấy phần giá
            $priceText = preg_replace('/(dưới|không quá|tối đa|<=|<)(\s)*/', '', $matches[0]);
            $priceInfo['max_price'] = $this->convertPriceTextToNumber($priceText);
        }
        
        // Tìm giá tối thiểu
        // Mẫu: "trên 100.000" hoặc "từ 100.000" hoặc "tối thiểu 100.000"
        $minPricePattern = '/(trên|từ|tối thiểu|>=|>)(\s)*(\d+[k\.]?\d*\s*(?:nghìn|ngàn|k|đồng|vnd|đ|triệu|tr)?)/iu';
        if (preg_match($minPricePattern, $message, $matches) && $priceInfo['price_type'] !== 'range') {
            $priceInfo['has_price'] = true;
            $priceInfo['price_type'] = 'min';
            
            // Lấy phần giá
            $priceText = preg_replace('/(trên|từ|tối thiểu|>=|>)(\s)*/', '', $matches[0]);
            $priceInfo['min_price'] = $this->convertPriceTextToNumber($priceText);
        }
        
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
     * Tìm kiếm sản phẩm dựa trên từ khóa
     *
     * @param  string  $keywords
     * @param  array   $priceInfo
     * @return array
     */
    private function searchProducts($keywords, $priceInfo = null)
    {
        try {
            Log::info('Tìm kiếm sản phẩm với từ khóa: ' . $keywords);
            if ($priceInfo && $priceInfo['has_price']) {
                Log::info('Thông tin giá: ', $priceInfo);
            }
            
            // Tách từ khóa thành các phần riêng biệt
            $keywordParts = explode(' ', $keywords);
            
            // Bắt đầu truy vấn
            $query = Product::with(['category', 'gallery']);
            
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
            
            // Các từ khóa thể hiện tìm kiếm theo không gian
            $spaceSearchTerms = ['phòng', 'không gian', 'khu vực', 'nơi'];
            $isSpaceSearch = false;
            
            foreach ($spaceSearchTerms as $term) {
                if (mb_strpos(mb_strtolower($keywords, 'UTF-8'), $term) !== false) {
                    $isSpaceSearch = true;
                    break;
                }
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
                if (empty($detectedSpaces) && $isSpaceSearch) {
                    // Thử tìm các từ gợi ý không gian khác
                    $possibleSpaceHints = [];
                    
                    // Các từ khóa phụ thường đi kèm với các loại phòng
                    $spaceHints = [
                        'khách' => 'phòng khách',
                        'ngủ' => 'phòng ngủ',
                        'ăn' => 'phòng ăn',
                        'làm việc' => 'phòng làm việc',
                        'tắm' => 'phòng tắm',
                        'bếp' => 'phòng ăn',
                        'nấu ăn' => 'phòng ăn',
                        'tiếp khách' => 'phòng khách'
                    ];
                    
                    foreach ($spaceHints as $hint => $space) {
                        if (mb_strpos(mb_strtolower($keywords, 'UTF-8'), $hint) !== false) {
                            $possibleSpaceHints[] = $space;
                        }
                    }
                    
                    // Thêm các sản phẩm liên quan nếu tìm thấy gợi ý không gian
                    foreach ($possibleSpaceHints as $space) {
                        if (isset($spaceToProductMapping[$space])) {
                            $relatedProductTerms = array_merge($relatedProductTerms, $spaceToProductMapping[$space]);
                            
                            // Thêm từ khóa không gian tiếng Anh để tìm kiếm rộng hơn
                            if (isset($spaceMapping[$space])) {
                                $keywordParts = array_merge($keywordParts, $spaceMapping[$space]);
                            }
                        }
                    }
                    
                    // Nếu vẫn không tìm thấy, mặc định là phòng khách
                    if (empty($possibleSpaceHints) && mb_strpos(mb_strtolower($keywords, 'UTF-8'), 'phòng') !== false) {
                        Log::info('Không xác định được loại phòng, mặc định là phòng khách');
                        $relatedProductTerms = array_merge($relatedProductTerms, $spaceToProductMapping['phòng khách']);
                        $keywordParts = array_merge($keywordParts, $spaceMapping['phòng khách']);
                    }
                }
                
                Log::info('Mở rộng tìm kiếm sang các sản phẩm liên quan: ' . implode(', ', $relatedProductTerms));
                
                // Thêm các sản phẩm liên quan vào danh sách tìm kiếm
                foreach ($relatedProductTerms as $term) {
                    foreach ($productCategories as $category => $terms) {
                        if (in_array($term, $terms) || mb_strpos($term, $category) !== false) {
                            if (!in_array($category, $specificCategories)) {
                                $specificCategories[] = $category;
                            }
                        }
                    }
                    
                    // Nếu không khớp với bất kỳ danh mục nào, thêm trực tiếp vào từ khóa tìm kiếm
                    $keywordParts[] = $term;
                }
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
            
            // Nếu tìm thấy danh mục cụ thể, lọc sản phẩm theo các danh mục đó
            if (!empty($specificCategories)) {
                $query->where(function($mainQuery) use ($specificCategories, $productCategories) {
                    foreach ($specificCategories as $category) {
                        $mainQuery->orWhereHas('category', function($q) use ($category, $productCategories) {
                            $categoryTerms = $productCategories[$category];
                            $q->where(function($subQ) use ($categoryTerms) {
                                foreach ($categoryTerms as $term) {
                                    $subQ->orWhere('name', 'like', '%' . $term . '%');
                                }
                            });
                        });
                    }
                });
            } else {
                // Nếu có nhiều từ khóa, sử dụng mỗi từ khóa để tìm kiếm
                if (count($keywordParts) > 1) {
                    $query->where(function($q) use ($keywordParts) {
                        foreach ($keywordParts as $part) {
                            if (mb_strlen($part) >= 2) { // Chỉ tìm kiếm với từ khóa có ít nhất 2 ký tự
                                $q->orWhere('name', 'like', '%' . $part . '%')
                                  ->orWhere('description', 'like', '%' . $part . '%')
                                  ->orWhereHas('category', function($categoryQuery) use ($part) {
                                      $categoryQuery->where('name', 'like', '%' . $part . '%');
                                  });
                            }
                        }
                    });
                } else {
                    // Nếu chỉ có một từ khóa, tìm kiếm trực tiếp
                    $query->where(function($q) use ($keywords) {
                        $q->where('name', 'like', '%' . $keywords . '%')
                          ->orWhere('description', 'like', '%' . $keywords . '%')
                          ->orWhereHas('category', function($categoryQuery) use ($keywords) {
                              $categoryQuery->where('name', 'like', '%' . $keywords . '%');
                          });
                    });
                }
            }
            
            // Nếu có thông tin giá, áp dụng bộ lọc giá
            if ($priceInfo && $priceInfo['has_price']) {
                // Xử lý giá chính xác
                if ($priceInfo['price_type'] === 'exact' && $priceInfo['exact_price'] !== null) {
                    // Tìm sản phẩm có giá xấp xỉ (sai số 15%)
                    $exactPrice = $priceInfo['exact_price'];
                    $minPrice = $exactPrice * 0.85; // Giảm 15%
                    $maxPrice = $exactPrice * 1.15; // Tăng 15%
                    
                    Log::info("Tìm sản phẩm với giá chính xác: {$exactPrice}, phạm vi [{$minPrice} - {$maxPrice}]");
                    
                    $query->where(function($q) use ($minPrice, $maxPrice) {
                        $q->where(function($subQ) use ($minPrice, $maxPrice) {
                            $subQ->whereBetween('price', [$minPrice, $maxPrice]);
                        })->orWhere(function($subQ) use ($minPrice, $maxPrice) {
                            $subQ->whereBetween('discount_price', [$minPrice, $maxPrice])
                                 ->whereNotNull('discount_price');
                        });
                    });
                }
                
                // Xử lý phạm vi giá
                if ($priceInfo['price_type'] === 'range') {
                    if ($priceInfo['min_price'] !== null && $priceInfo['max_price'] !== null) {
                        $minPrice = $priceInfo['min_price'];
                        $maxPrice = $priceInfo['max_price'];
                        
                        Log::info("Tìm sản phẩm với phạm vi giá: [{$minPrice} - {$maxPrice}]");
                        
                        $query->where(function($q) use ($minPrice, $maxPrice) {
                            // Kiểm tra giá gốc trong khoảng
                            $q->where(function($subQ) use ($minPrice, $maxPrice) {
                                $subQ->whereBetween('price', [$minPrice, $maxPrice]);
                            })
                            // HOẶC giá khuyến mãi trong khoảng
                            ->orWhere(function($subQ) use ($minPrice, $maxPrice) {
                                $subQ->whereBetween('discount_price', [$minPrice, $maxPrice])
                                     ->whereNotNull('discount_price');
                            });
                        });
                    }
                }
                
                // Xử lý giá tối đa
                if ($priceInfo['price_type'] === 'max' && $priceInfo['max_price'] !== null) {
                    $maxPrice = $priceInfo['max_price'];
                    Log::info("Tìm sản phẩm với giá tối đa: {$maxPrice}");
                    
                    $query->where(function($q) use ($maxPrice) {
                        $q->where(function($subQ) use ($maxPrice) {
                            $subQ->where('price', '<=', $maxPrice);
                        })->orWhere(function($subQ) use ($maxPrice) {
                            $subQ->where('discount_price', '<=', $maxPrice)
                                 ->whereNotNull('discount_price');
                        });
                    });
                }
                
                // Xử lý giá tối thiểu
                if ($priceInfo['price_type'] === 'min' && $priceInfo['min_price'] !== null) {
                    $minPrice = $priceInfo['min_price'];
                    Log::info("Tìm sản phẩm với giá tối thiểu: {$minPrice}");
                    
                    $query->where(function($q) use ($minPrice) {
                        // Nếu có giá khuyến mãi, dùng giá khuyến mãi để so sánh
                        $q->where(function($subQ) use ($minPrice) {
                            $subQ->where('price', '>=', $minPrice);
                        })->orWhere(function($subQ) use ($minPrice) {
                            $subQ->where('discount_price', '>=', $minPrice)
                                 ->whereNotNull('discount_price');
                        });
                    });
                }
            }

            // Lấy kết quả
            $products = $query->orderBy('created_at', 'desc')
                ->limit(15) // Tăng giới hạn để hiển thị nhiều sản phẩm hơn khi tìm nhiều danh mục
                ->get();

            
            Log::info('Tìm thấy ' . $products->count() . ' sản phẩm');
            
            // Phân loại sản phẩm theo danh mục
            $categorizedProducts = [];
            $foundCategories = [];
            $inPriceRange = false; // Biến kiểm tra có sản phẩm nào trong khoảng giá không
            $productsInPriceRange = []; // Mảng lưu các sản phẩm trong khoảng giá
            
            // Định dạng lại dữ liệu sản phẩm để hiển thị trong chat
            $formattedProducts = $products->map(function ($product) use (&$categorizedProducts, &$foundCategories, &$inPriceRange, &$productsInPriceRange, $priceInfo) {
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
                
                // Lấy giá hiển thị (ưu tiên giá khuyến mãi nếu có)
                $displayPrice = ($product->discount_price && $product->discount_price > 0) ? $product->discount_price : $product->price;
                
                // Kiểm tra xem sản phẩm có trong khoảng giá không (nếu có yêu cầu giá)
                $productInPriceRange = false;
                
                if ($priceInfo && $priceInfo['has_price']) {
                    if ($priceInfo['price_type'] === 'exact') {
                        $exactPrice = $priceInfo['exact_price'];
                        $minPrice = $exactPrice * 0.85;
                        $maxPrice = $exactPrice * 1.15;
                        $productInPriceRange = ($displayPrice >= $minPrice && $displayPrice <= $maxPrice);
                    } elseif ($priceInfo['price_type'] === 'range') {
                        $minPrice = $priceInfo['min_price'];
                        $maxPrice = $priceInfo['max_price'];
                        $productInPriceRange = ($displayPrice >= $minPrice && $displayPrice <= $maxPrice);
                    } elseif ($priceInfo['price_type'] === 'min') {
                        $minPrice = $priceInfo['min_price'];
                        $productInPriceRange = ($displayPrice >= $minPrice);
                    } elseif ($priceInfo['price_type'] === 'max') {
                        $maxPrice = $priceInfo['max_price'];
                        $productInPriceRange = ($displayPrice <= $maxPrice);
                    }
                    
                    if ($productInPriceRange) {
                        $inPriceRange = true;
                    }
                } else {
                    // Nếu không có yêu cầu giá, coi như sản phẩm phù hợp
                    $productInPriceRange = true;
                }
                
                // Đưa danh mục vào metadata sản phẩm
                $formattedProduct = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'image' => $imagePath,
                    'category' => $categoryName,
                    'category_group' => $categoryName,
                    'description' => Str::limit($product->description, 100),
                    'in_price_range' => $productInPriceRange,
                    'display_price' => $displayPrice // Thêm giá hiển thị để dễ dàng sắp xếp
                ];
                
                // Chỉ lưu sản phẩm vào mảng kết quả nếu phù hợp với yêu cầu giá
                // hoặc nếu không có yêu cầu về giá
                if ($productInPriceRange) {
                    // Phân loại sản phẩm vào nhóm danh mục
                    if (!isset($categorizedProducts[$categoryName])) {
                        $categorizedProducts[$categoryName] = [];
                    }
                    
                    $categorizedProducts[$categoryName][] = $formattedProduct;
                    
                    // Thêm vào mảng sản phẩm phù hợp giá
                    $productsInPriceRange[] = $formattedProduct;
                    
                    return $formattedProduct;
                }
                
                // Nếu không đáp ứng yêu cầu giá, trả về null
                return null;
            })
            ->filter(function ($product) {
                // Lọc bỏ các giá trị null (sản phẩm không đáp ứng yêu cầu giá)
                return $product !== null;
            })
            ->values(); // Chuẩn hóa lại index của mảng
            
            // Sắp xếp sản phẩm dựa vào yêu cầu giá
            $sortedProducts = $formattedProducts;
            
            if ($priceInfo && $priceInfo['has_price']) {
                // Nếu tìm với giá tối thiểu (min), sắp xếp từ cao đến thấp
                if ($priceInfo['price_type'] === 'min') {
                    // Sắp xếp sản phẩm từ giá cao nhất đến thấp nhất
                    usort($productsInPriceRange, function($a, $b) {
                        return $b['display_price'] - $a['display_price'];
                    });
                } 
                // Nếu tìm với giá tối đa (max), sắp xếp từ thấp đến cao
                else if ($priceInfo['price_type'] === 'max') {
                    // Sắp xếp sản phẩm từ giá thấp nhất đến cao nhất
                    usort($productsInPriceRange, function($a, $b) {
                        return $a['display_price'] - $b['display_price'];
                    });
                }
                
                $sortedProducts = $productsInPriceRange;
            }
            
            // Ghi log số lượng sản phẩm sau khi lọc giá
            Log::info('Số sản phẩm phù hợp với yêu cầu giá: ' . count($sortedProducts));
            
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
                'products' => $sortedProducts, // Trả về danh sách đã sắp xếp
                'categories' => $foundCategories,
                'categorized_products' => $sortedCategorizedProducts, // Trả về danh sách đã nhóm và sắp xếp
                'has_products_in_price_range' => $inPriceRange
            ];
        } catch (\Exception $e) {
            Log::error('Error searching products: ' . $e->getMessage());
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
                
                $expertPrompt .= "\n\nĐây là yêu cầu tìm kiếm sản phẩm với từ khóa: \"$searchKeywords\".";
                
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

}
