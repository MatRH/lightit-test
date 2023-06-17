<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

use App\Models\Patient;
use App\Jobs\SendEmail;
use Carbon\Carbon;

class PatientController extends Controller
{
    /**
     * Return the stored data for a given patient.
    */
    public function show(string $id): array
    {
        return ['patient' => Patient::findOrFail($id)];
    }

    /**
     * Validate and store a patients data.
    */
    public function store(Request $request): array
    {
        $response = array('response' => 'An unexpected error ocurred', 'success' => false);
        
        // Validate the request data
        $rules = [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:App\Models\Patient,email',
            'phone' => 'required|regex:/^\+?(\d{2,5})?[-, ]?\d{4}[-, ]?\d{4}$/i|max:16',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // Data is invalid return error
            $response['response'] = $validator->messages();
        } else {
            // Save the patients data
            $patient = new Patient;
    
            $patient->name = $request->name;
            $patient->last_name = $request->last_name;
            $patient->email = $request->email;
            $patient->phone = $request->phone;
            $patient->photo = $request->photo;
    
            $patient->save();

            // Check if a photo was passed
            if($request->hasFile('photo')) {
                $file = $request->file('photo');
                if(!$file->isValid()) {
                    $response['response'] = "Invalid file";
                    return $response;
                }
                if($file->extension() != "jpg") {
                    $response['response'] = "Invalid file extension";
                    return $response;
                }
                $hashed_name = $file->hashName();
                Storage::put($hashed_name, $file, 'public');
                $url = Storage::url($hashed_name);
                $patient->photo = $url;
                $patient->update();
            }

            $response['response'] = "Patient successfully registered";
            $response['success'] = true;

            $details = ['email' => $patient->email];
            $emailJob = (new SendEmail($details))->delay(Carbon::now()->addMinutes(1));
            dispatch($emailJob);

            /*
            $patient_email = $patient->email;
            $patient_name = $patient->name;
            dispatch(function ($email, $name) use ($patient_email, $patient_name){
                Mail::to($email)->send(new WelcomeMail($name));
            })->afterResponse();
            */
            /*
            Mail::mailer('smtp')
                ->to($patient->email)
                ->queue(new WelcomeMail("Jon"));
            */
        }
        
        return $response;

    }
}
