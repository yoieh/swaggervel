<?php

namespace App\Http\Controllers;

use App\Entities\User;
use App\Http\Requests\User\ForgotPasswordFormRequest;
use App\Http\Requests\User\RegistrationFormRequest;
use App\Http\Requests\User\UpdateFormRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    /**
     * @SWG\Get(
     *      path="/api/user",
     *      tags={"user"},
     *      summary="Retrieve the user's information",
     *      @SWG\Parameter(ref="#/parameters/oAuth2AccessToken"),
     *      @SWG\Response(
     *          response="200",
     *          description="User object is returned",
     *          @SWG\Schema(
     *             ref="#/definitions/User"
     *          )
     *      ),
     *      @SWG\Response(response="401", description="Invalid/missing oAuth2 Access Token"),
     * )
     * @param Request $request
     * @return User
     */
    public function index(Request $request)
    {
        return $request->user();
    }

    /**
     * @SWG\Post(
     *      path="/api/user/register",
     *      tags={"auth"},
     *      summary="Register a new user account",
     *      @SWG\Parameter(name="body", in="body", required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="name", type="string", example="Testi Tester"),
     *              @SWG\Property(property="email", type="string", example="testi.tester@gmail.com"),
     *              @SWG\Property(property="password", type="string", example="supersicher1234")
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Successfully created the user - you may now login"
     *      ),
     *      @SWG\Response(
     *          response="422",
     *          description="The validation has failed",
     *          @SWG\Schema(ref="#/definitions/ValidationMessageBag")
     *      )
     * )
     * @param RegistrationFormRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(RegistrationFormRequest $request)
    {
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
        ]);

        event(new Registered($user));

        return response('');
    }

    /**
     * @SWG\Post(
     *      path="/api/user/forgotPassword",
     *      tags={"user"},
     *      summary="Recover the user's password",
     *      @SWG\Parameter(name="body", in="body", required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="email", type="string", example="valentin.vergesslich@gmail.com"),
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="An email with a reset link has been sent.",
     *      ),
     *      @SWG\Response(
     *          response="422",
     *          description="An error occurred",
     *          @SWG\Schema(ref="#/definitions/ValidationMessageBag")
     *      )
     * )
     *
     * @param ForgotPasswordFormRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function forgotPassword(ForgotPasswordFormRequest $request)
    {
        $passwordBroker = Password::broker();
        $response = $passwordBroker->sendResetLink(
            $request->only('email')
        );

        if ($response === Password::RESET_LINK_SENT) {
            return response('');
        }

        return response(['email' => trans($response)], 422);
    }
}