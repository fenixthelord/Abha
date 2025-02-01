<?php

namespace App\Http\Traits;

use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

trait ResponseTrait
{


    public function apiResponse($data, $status, $message, $statusCode)
    {
        return response()->json([
            'status' => $status,
            'code' => $statusCode,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    /**
     * Return Error function
     *
     * @param string $msg
     * @return Response
     */
    public function returnError($msg)
    {
        return $this->apiResponse(null, false, $msg, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Return Success Message function
     *
     * @param string $msg
     * @return Response
     */
    public function returnSuccessMessage($msg = '')
    {
        return $this->apiResponse(null, true, $msg, Response::HTTP_OK);


        // return response()->json([
        //     'status' => true,
        //     'code' => Response::HTTP_OK,
        //     'msg' => $msg,
        // ], Response::HTTP_OK);
    }

    /**
     * Return Data function
     *
     * @param string $key
     * @param array $value
     * @param string $msg
     * @return Response
     */
    public function returnData($data, $msg = '')
    {
        return $this->apiResponse($data, true, $msg, Response::HTTP_OK);
    }

    /**
     * Return Validation Errors function
     *
     * @param Validator $validator
     * @return Response
     */
    public function returnValidationError($validator, $code = 400,)
    {
        return $this->apiResponse(null, false, $validator->errors()->first(), $code);
    }

    public function returnCodeAccordingToInput($validator)
    {
        $inputs = array_keys($validator->errors()->toArray());
        $code = $this->getErrorCodeValidation($inputs[0]);

        return $code;
    }

    public function getErrorCodeValidation($input)
    {
        if ($input == 'name') {
            return 001;
        } elseif ($input == 'password') {
            return 002;
        } elseif ($input == 'mobile') {
            return 003;
        } elseif ($input == 'id_number') {
            return 004;
        } elseif ($input == 'birth_date') {
            return 005;
        } elseif ($input == 'agreement') {
            return 006;
        } elseif ($input == 'email') {
            return 007;
        } elseif ($input == 'activation_code') {
            return 010;
        } elseif ($input == 'longitude') {
            return 011;
        } elseif ($input == 'latitude') {
            return 012;
        } elseif ($input == 'id') {
            return 013;
        } elseif ($input == 'promocode') {
            return 014;
        } elseif ($input == 'doctor_id') {
            return 015;
        } elseif ($input == 'payment_method' || $input == 'payment_method_id') {
            return 016;
        } elseif ($input == 'day_date') {
            return 017;
        } elseif ($input == 'type') {
            return 020;
        } elseif ($input == 'message') {
            return 021;
        } elseif ($input == 'reservation_no') {
            return 022;
        } elseif ($input == 'reason') {
            return 023;
        } elseif ($input == 'branch_no') {
            return 024;
        } elseif ($input == 'name_en') {
            return 025;
        } elseif ($input == 'name_ar') {
            return 026;
        } elseif ($input == 'gender') {
            return 027;
        } elseif ($input == 'rate') {
            return 030;
        } elseif ($input == 'price') {
            return 031;
        } elseif ($input == 'information_en') {
            return 032;
        } elseif ($input == 'information_ar') {
            return 033;
        } elseif ($input == 'street') {
            return 034;
        } elseif ($input == 'branch_id') {
            return 035;
        } elseif ($input == 'insurance_companies') {
            return 036;
        } elseif ($input == 'photo') {
            return 037;
        } elseif ($input == 'insurance_companies') {
            return 040;
        } elseif ($input == 'reservation_period') {
            return 041;
        } elseif ($input == 'nationality_id') {
            return 042;
        } elseif ($input == 'commercial_no') {
            return 043;
        } elseif ($input == 'nickname_id') {
            return 044;
        } elseif ($input == 'reservation_id') {
            return 045;
        } elseif ($input == 'attachments') {
            return 046;
        } elseif ($input == 'summary') {
            return 047;
        } elseif ($input == 'paid') {
            return 050;
        } elseif ($input == 'use_insurance') {
            return 051;
        } elseif ($input == 'doctor_rate') {
            return 052;
        } elseif ($input == 'provider_rate') {
            return 053;
        } elseif ($input == 'message_id') {
            return 054;
        } elseif ($input == 'hide') {
            return 055;
        } elseif ($input == 'checkoutId') {
            return 056;
        } else {
            return 422;
        }
    }
    /*
    * 400
    */
    public function badRequest($msg = '')
    {
        return $this->apiResponse(null, false, $msg, Response::HTTP_BAD_REQUEST);
    }
    /*
    * 403
    */
    public function Forbidden($msg = '')
    {
        return $this->apiResponse(null, false, $msg, Response::HTTP_FORBIDDEN);
    }
    /**
     * 401
     */
    public function Unauthorized($msg = '')
    {
        return $this->apiResponse(null, false, $msg, Response::HTTP_UNAUTHORIZED);
    }
    public function NotFound($msg = '')
    {
        return $this->apiResponse(null, false, $msg, Response::HTTP_NOT_FOUND);
    }

    public function PaginateData(array $data, $object)
    {
        $data['current_page'] = $object->currentPage();        // $data[] = 
        $data['next_page'] = $object->nextPageUrl();
        $data['previous_page'] = $object->previousPageUrl();
        $data['total_pages'] = $object->lastPage();
        return $this->returnData($data);
    }


    public function handleException(\Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {

            $modelName = class_basename($e->getModel());
            return $this->NotFound("$modelName not found");
        } elseif ($e instanceof ValidationException) {

            $errors = $e->validator;
            return $this->returnValidationError($errors->first());
        } elseif ($e instanceof HttpResponseException) {

            return $e->getResponse();
        } elseif ($e instanceof \Illuminate\Database\QueryException) {

            return $this->handleQueryException($e);
        } else {

            return $this->returnError($e->getMessage());
        }
    }

    protected function handleQueryException(\Illuminate\Database\QueryException $e)
    {
        $errorCode = $e->errorInfo[1]; // Error code from the database
        // dd($e->errorInfo[1]);
        switch ($errorCode) {
            case 1062: // Duplicate entry Like Unique Email Twice 
                return $this->badRequest("Duplicate entry found.");

            case 1451: // Cannot delete or update due to foreign key constraint
                return $this->badRequest("Cannot delete or update as it is referenced elsewhere.");

            case 1452: // Cannot add or update a child row due to foreign key constraint
                return $this->badRequest("Foreign key constraint violation.");
            case 1644:
                return $this->badRequest("A category cannot be its own parent");

            default:
                return $this->returnError("Database error: " . $e->getMessage());
        }
    }
}
