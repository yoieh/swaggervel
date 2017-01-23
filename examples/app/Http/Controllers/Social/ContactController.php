<?php

namespace App\Http\Controllers\Social;

use App\Entities\Contact;
use App\Http\Controllers\Controller;
use App\Http\Requests\Social\BatchContactFormRequest;
use App\Http\Requests\Social\ContactFormRequest;
use App\Jobs\MapContactUser;
use App\Jobs\NormalizeUserContact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * @SWG\Get(
     *      path="/api/contact",
     *      tags={"social"},
     *      summary="Get contacts that have been matched on an existing user",
     *      @SWG\Parameter(ref="#/parameters/oAuth2AccessToken"),
     *      @SWG\Response(
     *          response="200",
     *          description="Contact objects are returned",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(
     *                  ref="#/definitions/Contact",
     *              ),
     *          ),
     *      ),
     *      @SWG\Response(response="401", description="Invalid/missing oAuth2 Access Token"),
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $request->user()->contacts()->with('emailAddresses', 'phoneNumbers')->get();
    }

    /**
     * @SWG\Post(
     *      path="/api/contact",
     *      tags={"social"},
     *      summary="Create a new contact",
     *      @SWG\Parameter(ref="#/parameters/oAuth2AccessToken"),
     *      @SWG\Parameter(name="body", in="body", required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="name", type="string", example="Harry Hirsch"),
     *              @SWG\Property(property="email_addresses", type="array",
     *                  @SWG\Items(type="object",
     *                      @SWG\Property(property="email_address", type="string", example="flori@web.de"),
     *                      @SWG\Property(property="label", type="string", example="Privat"),
     *                  ),
     *              ),
     *              @SWG\Property(property="phone_numbers", type="array",
     *                  @SWG\Items(type="object",
     *                      @SWG\Property(property="phone_number", type="string", example="131231231"),
     *                      @SWG\Property(property="label", type="string", example="Privat"),
     *                  )
     *              ),
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Successfully created the contact",
     *          @SWG\Schema(ref="#/definitions/Contact")
     *      ),
     *      @SWG\Response(
     *          response="422",
     *          description="The validation has failed",
     *          @SWG\Schema(ref="#/definitions/ValidationMessageBag")
     *      ),
     * )
     *
     * @param  ContactFormRequest $request
     * @return Contact
     */
    public function store(ContactFormRequest $request)
    {
        /** @var Contact $contact */
        // Create the user contact.
        $contact = $request->user()->contacts()->create($request->except(['emailAddresses', 'phoneNumbers']));

        // Attach email addresses
        if ($request->has('email_addresses')) {
            foreach ($request->get('email_addresses') as $emailAddress) {
                $contact->emailAddresses()->create($emailAddress);
            }
        }

        // Attach phone numbers
        if ($request->has('phone_numbers')) {
            foreach ($request->get('phone_numbers') as $phoneNumber) {
                $contact->phoneNumbers()->create($phoneNumber);
            }
        }

        // Dispatch the normalization and mapping job.
        $this->dispatch(new MapContactUser($contact));

        // reload.. for "reasons"
        return $contact->fresh(['emailAddresses', 'phoneNumbers']);
    }

    /**
     * @SWG\Post(
     *      path="/api/contact/batch",
     *      tags={"social"},
     *      summary="Batch create new contacts",
     *      @SWG\Parameter(ref="#/parameters/oAuth2AccessToken"),
     *      @SWG\Parameter(name="body", in="body", required=true,
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(property="name", type="string", example="Harry Hirsch"),
     *                  @SWG\Property(property="email_addresses", type="array",
     *                      @SWG\Items(type="object",
     *                          @SWG\Property(property="email_address", type="string", example="flori@web.de"),
     *                          @SWG\Property(property="label", type="string", example="Privat"),
     *                      ),
     *                  ),
     *                  @SWG\Property(property="phone_numbers", type="array",
     *                      @SWG\Items(type="object",
     *                          @SWG\Property(property="phone_number", type="string", example="12312312"),
     *                          @SWG\Property(property="label", type="string", example="Privat"),
     *                      )
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Successfully created the contact",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="count", type="integer", example="42"),
     *          )
     *      ),
     *      @SWG\Response(
     *          response="422",
     *          description="The validation has failed",
     *          @SWG\Schema(ref="#/definitions/ValidationMessageBag")
     *      ),
     * )
     *
     * @param  BatchContactFormRequest $request
     * @return Contact
     */
    public function batchStore(BatchContactFormRequest $request)
    {
        foreach ($request->all() as $attributes) {
            // Create the user contact.
            $contact = $request->user()->contacts()->create($attributes);
            // Attach email addresses
            if (array_key_exists('email_addresses', $attributes)) {
                foreach ($attributes['email_addresses'] as $emailAddress) {
                    $contact->emailAddresses()->create($emailAddress);
                }
            }

            // Attach phone numbers
            if (array_key_exists('phone_numbers', $attributes)) {
                foreach ($attributes['phone_numbers'] as $phoneNumber) {
                    $contact->phoneNumbers()->create($phoneNumber);
                }
            }

            // Dispatch the normalization and mapping job.
            $this->dispatch(new MapContactUser($contact));
        }

        return response(['count' => count($request->all())]);
    }

    /**
     * @SWG\Put(
     *      path="/api/contact/{id}",
     *      tags={"social"},
     *      summary="Update an existing new contact (will remove all email addresses and phone numbers and add the provided ones)",
     *      @SWG\Parameter(ref="#/parameters/oAuth2AccessToken"),
     *      @SWG\Parameter(name="body", in="body", required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="name", type="string", example="Harry Hirsch"),
     *              @SWG\Property(property="email_addresses", type="array",
     *                  @SWG\Items(type="object",
     *                      @SWG\Property(property="email_address", type="string", example="flori@web.de"),
     *                      @SWG\Property(property="label", type="string", example="Privat"),
     *                  ),
     *              ),
     *              @SWG\Property(property="phone_numbers", type="array",
     *                  @SWG\Items(type="object",
     *                      @SWG\Property(property="phone_number", type="string", example="12312312"),
     *                      @SWG\Property(property="label", type="string", example="Privat"),
     *                  )
     *              ),
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Successfully updated the contact",
     *          @SWG\Schema(ref="#/definitions/Contact")
     *      ),
     *      @SWG\Response(
     *          response="422",
     *          description="The validation has failed",
     *          @SWG\Schema(ref="#/definitions/ValidationMessageBag")
     *      ),
     * )
     *
     * @param  ContactFormRequest $request
     * @param Contact $contact
     * @return Contact
     */
    public function update(ContactFormRequest $request, Contact $contact)
    {
        // Create the user contact.
        $contact->update($request->all());

        $contact->emailAddresses()->delete();
        $contact->phoneNumbers()->delete();

        // Attach email addresses
        if ($request->has('email_addresses')) {
            foreach ($request->get('email_addresses') as $emailAddress) {
                $contact->emailAddresses()->create($emailAddress);
            }
        }

        // Attach phone numbers
        if ($request->has('phone_numbers')) {
            foreach ($request->get('phone_numbers') as $phoneNumber) {
                $contact->phoneNumbers()->create($phoneNumber);
            }
        }

        // Dispatch the normalization and mapping job.
        $this->dispatch(new MapContactUser($contact));

        return $contact;
    }

    /**
     * @SWG\Delete(
     *      path="/api/contact/{id}",
     *      tags={"social"},
     *      summary="Delete a specific contact",
     *      @SWG\Parameter(ref="#/parameters/oAuth2AccessToken"),
     *      @SWG\Parameter(name="id", in="path", type="integer"),
     *      @SWG\Response(response="200", description="Successfully deleted the contact"),
     *      @SWG\Response(response="401", description="Invalid/missing oAuth2 Access Token"),
     *      @SWG\Response(response="403", description="Missing authorization to access to requested entity"),
     *      @SWG\Response(response="404", description="Entity not found"),
     * )
     *
     * @param  Contact $contact
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);

        $contact->delete();
    }
}