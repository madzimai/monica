<?php

namespace App\Models\Account;

use Carbon\Carbon;
use App\Models\Contact\Account;
use App\Models\Contact\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundEmail extends Model
{
    protected $dates = [
        'sent',
        'created_at',
        'updated_at',
    ];

    protected $table = 'inbound_emails';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content',
        'subject',
        'to',
        'from',
        'account_id',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the user associated with the email.
     *
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the Contact records associated with the email.
     *
     * @return HasMany
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_inbound_email')->withPivot('account_id');
    }

    public function setSentAttribute($value)
    {
        $this->attributes['sent'] = Carbon::parse($value);
    }
}
