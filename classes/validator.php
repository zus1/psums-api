<?php


class Validator
{
    public function filterAlphaNumeric($value) {
        return $this->filter($value, "/[^A-Za-z0-9]/");
    }

    public function filterAlphaNumericDash($value) {
        return $this->filter($value, "/[^A-Za-z0-9]_ /");
    }

    public function filterNumeric($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public function filterAlpha($value) {
        return $this->filter($value, "/[^A-Za-z]/");
    }

    public function filterAlphaLatin($value) {
        return $this->filter($value, "/[^A-Za-zČĆŽŠĐčćžšđ]/");
    }

    public function filterUrl($value) {
        return filter_var($value, FILTER_SANITIZE_URL);
    }

    public function filterEmail($value) {
        $sanitizedEmail =  filter_var($value, FILTER_SANITIZE_EMAIL);
        if($sanitizedEmail !== $value) {
            return "";
        }
        if(!filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) {
            return "";
        }
        return $sanitizedEmail;
    }

    public function filterPassword($value) {
        return $this->filter($value, "/[^A-Za-z0-9_@.!\-+]/");
    }

    public function filterAlphaDash($value) {
        return $this->filter($value, "/[^A-Za-z_ ]/");
    }

    public function filterAlphaNumUnderscore($value) {
        return $this->filter($value, "/[^A-Za-z0-9_-]/");
    }

    public function filterHTML($value) {
        $plainText = strip_tags($value);
        $sanitized = $this->filter($plainText, "/[^A-Za-z0-9_ ?()!{}\"[].:&,=;\-\/]/");
        if($plainText !== $sanitized) {
            return $value . "_suffix"; //make it fail
        }

        return $value; //make it pass
    }

    public function filter($value, string $pattern) {
        return preg_replace($pattern, "", $value);
    }
}