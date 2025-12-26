<?php

namespace App\Traits;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;

trait ApiResponseTrait
{
    public function withSuccess($message = "success", $data = [], $statusCode = 200)
    {
        return $this->sendResponse(true, $message, $data, $statusCode);
    }

    public function withError($message, $data = [], $statusCode = 400)
    {
        return $this->sendResponse(false, $message, $data, $statusCode);
    }

    private function sendResponse($success, $message, $data, $statusCode)
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ];
        if (request()->ajax()) {
            return response()->json($response, $statusCode);
        } else {
            session()->flash($success ? "success" : "error", $message);
            return redirect();
        }
    }

    protected function error(string $message = '', int $status = 400, $strtoupper = true, array $errors = [])
    {
        $message = !empty($strtoupper) ? strtoupper($message) : $message;
        $response = ["status" =>  $status, "message" => $message, 'errors' => $errors];
        return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
    }
    protected function validation($v)
    {
        $errors            = $v->messages()->toArray();
        $error_description = "";
        foreach ( $v->messages()->all() as $error_message ) {
            $error_description .= $error_message . " ";
        }
        return $this->error($error_description, 400, true, $errors);
    }
}