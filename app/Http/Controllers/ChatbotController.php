<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LLMClient;
use App\Models\Product;

class ChatbotController extends Controller
{
    public function index() {
        return view('chat');
    }

    public function message(Request $req, LLMClient $cb)
    {
        try {
            $history = $req->input('history', []);
            $userMsg = $req->input('message', '');

            $system = require base_path('app/Prompts/Prompt.php');
            $messages = array_merge(
                [['role' => 'system', 'content' => $system]],
                $history,
                [['role' => 'user', 'content' => $userMsg]]
            );

            $resp = $cb->chat($messages, [
                'temperature' => 0.2,
                'max_tokens' => 800,
            ]);

            $content = $resp['choices'][0]['message']['content'] ?? '{}';
            $advisor = json_decode($content, true);
            if (!is_array($advisor)) {
                $advisor = ['message' => $content, 'filters' => []];
            }

            $q = Product::query()->with('category');
            $products = $q->orderBy('price')->limit(8)->get();

            return response()->json([
                'advisor'  => $advisor,
                'products' => $products,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Chat error', ['e' => $e]);
            return response()->json([
                'error' => 'server_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
