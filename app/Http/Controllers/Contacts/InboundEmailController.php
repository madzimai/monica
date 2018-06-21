<?php

namespace App\Http\Controllers;

use App\Account;
use App\InboundEmail;
use App\ContactFieldType;
use Illuminate\Http\Request;

class InboundEmailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('webhook');
    }

    public function receiveFromPostmark(Request $request)
    {
        if (empty($request->MailboxHash)) {
            abort(400);
        }

        $account = Account::findOrFailByRawHashID($request->MailboxHash);

        $contacts = $account->contacts()->real();

        $emailContent = $request->TextBody;

        $emailData = [
          'from_email' => '',
          'to_email' => '',
          'subject' => '',
          'datetime' => '',
        ];

        $pattern = '/From:.{1,}<(.{1,})>/m';
        if (preg_match($pattern, $emailContent, $matches)) {
            $emailData['from_email'] = $matches[1];
        } else {
            abort(400);
        }

        $pattern = '/To:.{1,}<(.{1,})>/m';
        if (preg_match($pattern, $emailContent, $matches)) {
            $emailData['to_email'] = $matches[1];
        } else {
            abort(400);
        }

        $pattern = '/Date:\s{1,}(.{1,})/m';
        if (preg_match($pattern, $emailContent, $matches)) {
            $emailData['datetime'] = $matches[1];
        } else {
            abort(400);
        }

        $pattern = '/Subject:\s{1,}(.{1,})/m';
        if (preg_match($pattern, $emailContent, $matches)) {
            $emailData['subject'] = $matches[1];
        } else {
            abort(400);
        }

        $contactFieldType = ContactFieldType::where('name', 'Email')->first();
        $contacts = $contacts->whereHas('contactFields', function ($query) use ($contactFieldType->id, $emailData) {
            $toEmail = $emailData['to_email'];
            $fromEmail = $emailData['from_email'];

            $query->where([
                ['data', "$toEmail"],
                ['contact_field_type_id', $contactFieldType->id],
            ])->orWhere([
                ['data', "$fromEmail"],
                ['contact_field_type_id', $contactFieldType->id],
            ]);
        })->get();

        $emailData['datetime'] = str_replace_first('at', '', $emailData['datetime']);

        $inboundEmail = new InboundEmail;
        $inboundEmail->account_id = $account->id;
        $inboundEmail->to = $emailData['to_email'];
        $inboundEmail->from = $emailData['from_email'];
        $inboundEmail->subject = $emailData['subject'];
        $inboundEmail->sent = $emailData['datetime'];
        $inboundEmail->content = $emailContent;
        $inboundEmail->save();

        foreach ($contacts as $contact) {
            $inboundEmail->contacts()->syncWithoutDetaching([$contact->id => ['account_id' => $this->account->id]])
        }

        return response('OK', 200);
    }
}
