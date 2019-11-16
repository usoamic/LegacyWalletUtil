<?php

/**
 * Consts
 */
const UNKNOWN_ERROR = 2;
const DATABASE_ERROR = 3;
const ADDRESS_REQUIRED = 5;
const AMOUNT_REQUIRED = 6;
const INVALID_AMOUNT = 7;
const UNKNOWN_SENDING_ERROR = 8;
const TOKENS_SENT = 9;
const INSUFFICIENT_FUNDS = 10;
const CONFIRMATION_CODE_REQUIRED = 11;
const SUCCESSFULLY_CHANGED = 13;
const AUTHENTICATOR_CODE_REQUIRED = 14;
const INVALID_AUTHENTICATOR_CODE = 15;
const INVALID_CONFIRMATION_CODE = 16;
const EMAIL_SUCCESSFULLY_CONFIRMED = 17;
const INVALID_ADDRESS = 18;
const INVALID_EMAIL_ADDRESS = 19;
const RESET_PASSWORD_EMAIL_SENT = 20;
const RESET_CODE_REQUIRED = 21;
const INVALID_RESET_CODE = 22;
const PASSWORD_UPDATE_ERROR = 23;
const NEW_PASSWORD_SENT = 24;
const USER_NOT_LOGGED = 25;
const PASSWORDS_DONT_MATCH = 26;
const CURRENT_PASSWORD_REQUIRED = 27;
const NEW_PASSWORD_REQUIRED = 28;
const INVALID_CURRENT_PASSWORD = 29;
const PASSWORD_CHANGED = 30;
const PASSWORD_REQUIRED = 31;
const CONFIRM_PASSWORD_REQUIRED = 32;
const EMAIL_NOT_CONFIRMED = 33;
const INVALID_EMAIL_OR_PASSWORD = 34;
const EMAIL_REQUIRED = 35;
const TERMS_OF_USE_REQUIRED = 36;
const EMAIL_ALREADY_REGISTERED = 37;
const INVALID_PASSWORD = 38;
const TFA_DISABLED = 39;
const TFA_ENABLED = 40;
const SENDING_DISABLED = 41;
const INVALID_CAPTCHA = 42;
const AMOUNT_MUST_BE_GREATER_THAN = 45;
const NA = 46;
const CURRENT_PASSWORD_AND_NEW_PASSWORD_MATCH = 47;

/**
 * Transaction status
 */
const TX_PENDING = 1;
const TX_CONFIRMED = 2;
const TX_REJECTED = 3;

/**
 * Transaction type
 */
const TX_ALL = 1;
const TX_LEGACY_SENT = 2;
const TX_MOVED = 3;
const TX_MINED = 4;
const TX_RECEIVED = 5;
const TX_UNKNOWN = 6;
const TX_SENT = 7;

function get_string($number)
{
    switch ($number) {
        case DATABASE_ERROR:
            $str = "Unknown database error";
            break;
        case ADDRESS_REQUIRED:
            $str = "Address required";
            break;
        case AMOUNT_REQUIRED:
            $str = "Amount required";
            break;
        case INVALID_AMOUNT:
            $str = "Invalid amount";
            break;
        case UNKNOWN_SENDING_ERROR:
            $str = "Unknown sending error";
            break;
        case TOKENS_SENT:
            $str = "Coins successfully sent";
            break;
        case INSUFFICIENT_FUNDS:
            $str = "Account has insufficient funds";
            break;
        case CONFIRMATION_CODE_REQUIRED:
            $str = "Confirmation code required";
            break;
        case SUCCESSFULLY_CHANGED:
            $str = "Successfully changed";
            break;
        case AUTHENTICATOR_CODE_REQUIRED:
            $str = "Authenticator code required";
            break;
        case INVALID_AUTHENTICATOR_CODE:
            $str = "Invalid Authenticator code";
            break;
        case INVALID_CONFIRMATION_CODE:
            $str = "Invalid confirmation code";
            break;
        case EMAIL_SUCCESSFULLY_CONFIRMED:
            $str = "E-Mail address successfully confirmed";
            break;
        case INVALID_ADDRESS:
            $str = "Invalid address";
            break;
        case INVALID_EMAIL_ADDRESS:
            $str = "Invalid email address";
            break;
        case RESET_PASSWORD_EMAIL_SENT:
            $str = "You will receive an email with reset password instructions";
            break;
        case RESET_CODE_REQUIRED:
            $str = "Reset code required";
            break;
        case INVALID_RESET_CODE:
            $str = "Invalid reset code";
            break;
        case PASSWORD_UPDATE_ERROR:
            $str = "Error updating password";
            break;
        case NEW_PASSWORD_SENT:
            $str = "You will receive an email with new password";
            break;
        case USER_NOT_LOGGED:
            $str = "User not logged";
            break;
        case PASSWORDS_DONT_MATCH:
            $str = "Passwords do not match";
            break;
        case CURRENT_PASSWORD_REQUIRED:
            $str = "Current Password required";
            break;
        case NEW_PASSWORD_REQUIRED:
            $str = "New password required";
            break;
        case INVALID_CURRENT_PASSWORD:
            $str = "Invalid current password";
            break;
        case PASSWORD_CHANGED:
            $str = "Password successfully changed";
            break;
        case PASSWORD_REQUIRED:
            $str = "Password required";
            break;
        case CONFIRM_PASSWORD_REQUIRED:
            $str = "Confirm password required";
            break;
        case EMAIL_NOT_CONFIRMED:
            $str = "E-Mail is not confirmed";
            break;
        case INVALID_EMAIL_OR_PASSWORD:
            $str = "Invalid email or password";
            break;
        case EMAIL_REQUIRED:
            $str = "E-Mail address required";
            break;
        case TERMS_OF_USE_REQUIRED:
            $str = "You must agree to the terms of use";
            break;
        case EMAIL_ALREADY_REGISTERED:
            $str = "Email already registered";
            break;
            break;
        case INVALID_PASSWORD:
            $str = 'Invalid password';
            break;
        case TFA_ENABLED:
            $str = '2FA Enabled';
            break;
        case TFA_DISABLED:
            $str = '2FA Disabled';
            break;
        case SENDING_DISABLED:
            $str = 'Sending tokens disabled';
            break;
        case INVALID_CAPTCHA:
            $str = "Invalid captcha";
            break;
        case NA:
            $str = "N/A";
            break;
        case AMOUNT_MUST_BE_GREATER_THAN:
            $str = "Amount must be greater than or equal to " . MIN_TX_AMOUNT . " " . TOKEN_TICKER;
            break;
        case CURRENT_PASSWORD_AND_NEW_PASSWORD_MATCH:
            $str = "Current password and new password match";
            break;
        default:
            $str = 'Unknown error';
            break;
    }
    return $str;
}

function getTransactionStatus($number)
{
    switch ($number) {
        case TX_PENDING:
            $str = "Pending";
            break;
        case TX_CONFIRMED:
            $str = "Confirmed";
            break;
        case TX_REJECTED:
            $str = "Rejected";
            break;
        default:
            $str = "N/A";
            break;
    }
    return $str;
}

function getTransactionType($number)
{
    switch ($number) {
        case TX_RECEIVED:
            $str = "Received";
            break;
        case TX_LEGACY_SENT:
            $str = "Sent (coin)";
            break;
        case TX_SENT:
            $str = "Sent (token)";
            break;
        case TX_MOVED:
            $str = "Moved";
            break;
        case TX_MINED:
            $str = "Mined";
            break;
        default:
            $str = "N/A";
            break;
    }
    return $str;
}

?>