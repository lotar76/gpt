<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIChart\AIChatInterface;
use App\Services\AIChart\PromptResolver;

class AIChatController extends Controller
{
    public function __construct(
        private readonly AIChatInterface $chat,
        private readonly PromptResolver $resolver,
    ) {}

    public function handle(Request $request)
    {
  $request->validate([
            'prompt' => 'required|string',
            'image' => 'nullable|image|max:10240', // до 10 MB
        ]);

        $resolvedPrompt = $this->resolver->resolve($request->input('prompt'));

        $imageBase64 = null;

        if ($request->hasFile('image')) {
            $imageBase64 = base64_encode(
                file_get_contents($request->file('image')->getRealPath())
            );
        }

        try {
            $result = $this->chat->sendPrompt($resolvedPrompt, $imageBase64);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
