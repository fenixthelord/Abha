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

    public function PaginateData(array $data, $object) {
        $data['next_page'] = $object->nextPageUrl();
        $data['current_page'] = $object->currentPage();        // $data[] =
        $data['previous_page'] = $object->previousPageUrl();
        $data['total_pages'] = $object->lastPage();
        return $this->returnData($data);
    }


    public function handleException(\Exception $e) {
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

    protected function handleQueryException(\Illuminate\Database\QueryException $e) {
        $errorCode = $e->errorInfo[1]; // Error code from the database
        // dd($e->errorInfo[1]);
        switch ($errorCode) {
            case 1062: // Duplicate entry Like Unique Email Twice
                return $this->badRequest('Duplicate entry found');

            case 1451: // Cannot delete or update due to foreign key constraint
                return $this->badRequest('Cannot delete or update as it is referenced elsewhere');

            case 1452: // Cannot add or update a child row due to foreign key constraint
                return $this->badRequest('Foreign key constraint violation');
            case 1644:
                return $this->badRequest('A category cannot be its own parent');

            default:
                return $this->returnError('Database error: ' . $e->getMessage());
        }
    }
}
