<?php

namespace App\Http\Traits;

use App\Models\ContactDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;



trait GeneralTrait
{
    public function apiResponse($data = null, bool $status = true, $error = null, $statusCode = 200)
    {
        $array = [
            'data' => $data,
            'status' => $status,
            'error' => $error,
            'statusCode' => $statusCode
        ];
        return response($array, $statusCode);
    }

    public function unAuthorizeResponse()
    {
        return $this->apiResponse(null, 0, 'Unauthorize', 401);
    }

    public function notFoundResponse($more)
    {
        return $this->apiResponse(null, 0, $more, 404);
    }

    public function requiredField($message)
    {
        // return $this->apiResponse(null, false, $message, 200);
        return $this->apiResponse(null, false, $message, 400);
    }

    public function forbiddenResponse()
    {
        return $this->apiResponse(null, false, 'Forbidden', 403);
    }

    public function SuccessResponse($data = NULL, $statusCode = 200)
    {
        return $this->apiResponse($data, True, NULL, $statusCode);
    }

    public function handleException(\Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $modelName = class_basename($e->getModel());
            return $this->notFoundResponse("$modelName not found");
        } elseif ($e instanceof ValidationException) {
            $errors = $e->validator->errors();
            return $this->requiredField($errors->first());
        } else if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } else {
            return $this->apiResponse(null, false, $e->getMessage(), 500);
        }
    }

    public function ValidationError($data = null, $validator)
    {
        return $this->apiResponse(null, false, $validator->errors()->first(), 400);
    }



    /**  public function send_email($templateName, $email1, $subj, $order)
    {
        try {


            Mail::send($templateName, $order, function ($message) use ($email1, $subj) {
                $message->to($email1, 'Blue')->subject($subj);
                // $message->from('biners.testing@gmail.com', 'Insurance');
            });
            return true;
        } catch (\Swift_TransportException $e) {

            return false;
        } catch (\Exception $e) {


            return false;
        }

    }
     */
}
