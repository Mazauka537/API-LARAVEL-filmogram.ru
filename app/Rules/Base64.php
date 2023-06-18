<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class Base64 implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($memeTypes = [])
    {
        $this->memeTypes = $memeTypes;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $separatedBase64 = explode(',', $value);

        if (count($separatedBase64) !== 2) {
            return false;
        }

        $reg_exp = '/^data:(';
        foreach ($this->memeTypes as $memeType) {
            $reg_exp .= preg_quote($memeType, '/') . '|';
        }
        $reg_exp = trim($reg_exp, '|');
        $reg_exp .= ');base64$/';

        $isMatch = preg_match($reg_exp, $separatedBase64[0]);

        if (!$isMatch) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The field :attribute is invalid';
    }
}
