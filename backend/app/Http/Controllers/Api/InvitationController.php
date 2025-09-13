<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\MockableController;
use App\Models\Invitation;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'invitation creation');
        }

        $existingInvitation = Invitation::where('email', $request->email)->first();
        if ($existingInvitation) {
            return $this->businessLogicErrorResponse(
                'An invitation has already been sent to this email address.',
                'INVITATION_ALREADY_EXISTS',
                [
                    'email' => $request->email,
                    'existing_invitation_id' => $existingInvitation->id,
                    'existing_invitation_created' => $existingInvitation->created_at->toISOString()
                ],
                [
                    'Check if the user has already received an invitation',
                    'Consider resending the existing invitation if needed',
                    'Use a different email address if this is a different user'
                ]
            );
        }

        try {
            $role = Role::find($request->role_id);

            $invitation = Invitation::create([
                'email' => $request->email,
                'role_id' => $role->id,
                'organization_id' => auth()->user()->organization_id,
                'token' => Invitation::generateToken(),
            ]);

            Mail::to($request->email)->send(new InvitationMail($invitation));

            return $this->successResponse([
                'invitation_id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $role->name,
                'created_at' => $invitation->created_at->toISOString()
            ], 'Invitation sent successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->databaseErrorResponse($e, 'invitation creation', 'invitation');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('invitation creation', $e);
        }
    }

    public function validateToken(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            return $this->businessLogicErrorResponse(
                'Invitation token is required.',
                'MISSING_TOKEN',
                [],
                [
                    'Ensure the invitation link includes a valid token parameter',
                    'Check if the invitation link was copied correctly'
                ]
            );
        }

        try {
            $invitation = Invitation::where('token', $token)->with('organization')->first();

            if (!$invitation) {
                return $this->businessLogicErrorResponse(
                    'Invalid or expired invitation token.',
                    'INVALID_INVITATION_TOKEN',
                    [
                        'token' => $token
                    ],
                    [
                        'Request a new invitation from your organization administrator',
                        'Ensure you are using the most recent invitation link',
                        'Check if the invitation link was copied correctly'
                    ]
                );
            }

            return $this->successResponse([
                'email' => $invitation->email,
                'role' => $invitation->role->name,
                'organization' => $invitation->organization->only('id', 'name'),
                'token' => $invitation->token
            ], 'Invitation validated successfully');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('invitation validation', $e);
        }
    }
}
