<?php

/**
 * @SWG\Swagger(
 *      schemes={"http", "https"},
 *      consumes={"application/json"},
 *      produces={"application/json"},
 *      @SWG\Info(
 *          title="Example API",
 *          description="This is the API documentation of the first party API of example.com",
 *          version="0.1"
 *      ),
 *      @SWG\Parameter(parameter="oAuth2AccessToken", name="Authorization", in="header",
 *          description="oAuth2 Access Token", required=true, type="string", default="Bearer ..."),
 *  )
 *
 * @SWG\Definition(
 *      definition="ValidationMessageBag",
 *      type="object",
 *      @SWG\Property(property="fieldName", type="array", @SWG\Items(type="string", description="Messages", example="Email already taken!"))
 * )
 *
 * @SWG\Tag(name="user", description="All user-object related requests")
 * @SWG\Tag(name="auth", description="All authentication related requests")
 * @SWG\Tag(name="social", description="All social and community related requests")
 *
 * @SWG\Post(
 *      path="/oauth/token",
 *      tags={"auth"},
 *      summary="Retrieve a oAuth2 Access token for a user account",
 *      @SWG\Parameter(name="body", in="body", required=true,
 *          @SWG\Schema(
 *              type="object",
 *              @SWG\Property(property="grant_type", type="string", example="password"),
 *              @SWG\Property(property="client_id", type="integer", example="1"),
 *              @SWG\Property(property="client_secret", type="string", example="xx9TaaO25lPdajGJ0m2LRB1lasdYPGvjVb63U4F"),
 *              @SWG\Property(property="username", type="string", example="test@tester.de"),
 *              @SWG\Property(property="password", type="string", example="supersicher1234"),
 *              @SWG\Property(property="scope", type="string"),
 *          )
 *      ),
 *      @SWG\Response(
 *          response="200",
 *          description="Success",
 *          @SWG\Schema(
 *              type="object",
 *              @SWG\Property(property="token_type", type="string", example="Bearer"),
 *              @SWG\Property(property="expires_in", type="integer", example="31536000"),
 *              @SWG\Property(property="access_token", type="string"),
 *              @SWG\Property(property="refresh_token", type="string"),
 *          )
 *      ),
 *      @SWG\Response(
 *          response="401",
 *          description="Unauthorized",
 *     )
 * )
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
