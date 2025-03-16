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
                $keywords = $this->extractSearchKeywords($userMessage);

                
                // Tìm kiếm sản phẩm
                $products = $this->searchProducts($keywords);
                

                // Gọi API Gemini để có phản hồi thông minh
                // Truyền thêm thông tin về kết quả tìm kiếm để Gemini có thể đưa ra phản hồi phù hợp
                $aiResponse = $this->callGeminiApi($userMessage, [
                    'is_product_search' => true,
                    'search_keywords' => $keywords,
                    'found_products' => count($products) > 0,
                    'product_count' => count($products)
                ]);

                // Trả về cả phản hồi AI và sản phẩm
                return response()->json([
                    'success' => true,
                    'reply' => $aiResponse,
                    'has_products' => count($products) > 0,
                    'products' => $products,
                    'search_keywords' => $keywords
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
     * @return string
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
        

        // Tìm danh mục sản phẩm trong tin nhắn
        $foundCategory = null;
        foreach ($categories as $category => $id) {
            if (mb_strpos($message, $category) !== false) {
                $foundCategory = $category;
                break;
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
        
        if ($foundCategory) {
            $keywords[] = $foundCategory;
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
                return $message;
            }

            
            return $message;
        }
        

        // Kết hợp các từ khóa tìm được
        return implode(' ', $keywords);
    }

    /**
     * Tìm kiếm sản phẩm dựa trên từ khóa
     *
     * @param  string  $keywords
     * @return array
     */
    private function searchProducts($keywords)
    {
        try {
            Log::info('Tìm kiếm sản phẩm với từ khóa: ' . $keywords);

            
            // Tách từ khóa thành các phần riêng biệt
            $keywordParts = explode(' ', $keywords);
            
            // Bắt đầu truy vấn
            $query = Product::with(['category', 'gallery']);
            

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

            // Lấy kết quả
            $products = $query->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            
            Log::info('Tìm thấy ' . $products->count() . ' sản phẩm');
            

            // Nếu không tìm thấy sản phẩm nào, thử tìm kiếm lại với từng từ khóa riêng biệt
            if ($products->count() == 0 && count($keywordParts) > 1) {
                foreach ($keywordParts as $part) {
                    if (mb_strlen($part) >= 2) { // Chỉ tìm kiếm với từ khóa có ít nhất 2 ký tự
                        $partQuery = Product::with(['category', 'gallery'])
                            ->where('name', 'like', '%' . $part . '%')
                            ->orWhere('description', 'like', '%' . $part . '%')
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();

                        if ($partQuery->count() > 0) {
                            $products = $partQuery;
                            Log::info('Tìm thấy ' . $products->count() . ' sản phẩm với từ khóa: ' . $part);
                            break;
                        }
                    }
                }
            }

            // Định dạng lại dữ liệu sản phẩm để hiển thị trong chat
            return $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'image' => $product->image_thumnail,
                    'category' => $product->category ? $product->category->name : 'N/A',
                    'description' => Str::limit($product->description, 100)
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error searching products: ' . $e->getMessage());
            return [];
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
- Không trả lời quá dài dòng
- Đưa ra lời khuyên cụ thể và thực tế
- Nếu không biết câu trả lời, hãy thành thật và đề nghị khách hàng liên hệ với nhân viên tư vấn
- Không đưa ra thông tin sai lệch về sản phẩm hoặc dịch vụ";


            // Nếu đây là yêu cầu tìm kiếm sản phẩm, thêm hướng dẫn cụ thể
            if (isset($context['is_product_search']) && $context['is_product_search']) {
                $searchKeywords = $context['search_keywords'] ?? '';
                $foundProducts = $context['found_products'] ?? false;
                $productCount = $context['product_count'] ?? 0;
                
                $expertPrompt .= "\n\nĐây là yêu cầu tìm kiếm sản phẩm với từ khóa: \"$searchKeywords\".";
                

                if (!$foundProducts) {
                    $expertPrompt .= "\nKHÔNG tìm thấy sản phẩm nào phù hợp với từ khóa này trong cơ sở dữ liệu của chúng tôi.
Hãy bắt đầu câu trả lời của bạn bằng: \"Xin lỗi, hiện tại chúng tôi không có sản phẩm nào phù hợp với yêu cầu tìm kiếm của bạn.\"
Sau đó, bạn có thể đề xuất một số sản phẩm tương tự hoặc gợi ý khách hàng thử tìm kiếm với từ khóa khác.";
                } else {
                    $expertPrompt .= "\nĐã tìm thấy $productCount sản phẩm phù hợp với từ khóa này.
Hãy bắt đầu câu trả lời của bạn bằng: \"Tôi đã tìm thấy một số sản phẩm phù hợp với yêu cầu của bạn. Bạn có thể xem các sản phẩm bên dưới.\"
Sau đó, bạn có thể đưa ra một số gợi ý hoặc lời khuyên liên quan đến loại sản phẩm này.";
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
                    'maxOutputTokens' => 1000,
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

