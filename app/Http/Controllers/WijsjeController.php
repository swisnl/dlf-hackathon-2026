<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ConceptExplainer;
use App\Http\Requests\WijsjeQuestionRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Wijsje answers in the corner of every page; the conversation lives in the browser and is never stored (#31).
 */
class WijsjeController extends Controller
{
    public function __invoke(WijsjeQuestionRequest $request): JsonResponse
    {
        $this->allowSlowAi();

        try {
            $response = (new ConceptExplainer($request->history(), $request->exercise()))
                ->prompt(ConceptExplainer::question($request->question()));
        } catch (Throwable $e) {
            return response()->json([
                'message' => $this->aiFailed($e, 'Wijsje kon nu geen antwoord geven. Probeer het zo opnieuw.'),
            ], 503);
        }

        return response()->json(['reply' => trim($response->text)]);
    }
}
