<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Requests_Session;
use Requests_Response;

class Helpers
{
    public static function mockRequest(TestCase $testCase)
    {
        return $testCase->getMockBuilder(Requests_Session::class)
                        ->setMethods(['get'])
                        ->getMock();
    }

    public static function mockSuccessfulRequest(TestCase $testCase)
    {
        $request = self::mockRequest($testCase);
        $request->method('get')
                ->willReturn(self::successfulResponse());

        return $request;
    }

    public static function expectSuccessfulRequestToQueryString(TestCase $testCase, $queryString)
    {
        $request = self::mockSuccessfulRequest($testCase);
        $request->expects($testCase->once())
                ->method('get')
                ->with($testCase->stringEndsWith($queryString));

        return $request;
    }

    public static function mockFailureRequest(TestCase $testCase)
    {
        $request = self::mockRequest($testCase);
        $request->method('get')
                ->willReturn(self::failureResponse());

        return $request;
    }

    private static function successfulResponse()
    {
        $response = new Requests_Response();
        $response->body = '         1{"show":{"title":"Church of Monk"}}';
        $response->status_code = 200;
        $response->success = true;

        return $response;
    }

    private static function failureResponse()
    {
        $response = new Requests_Response();
        $response->status_code = 400;
        $response->success = false;

        return $response;
    }
}
