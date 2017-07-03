<?php namespace DiegoCaprioli\Larachimp\Models;

interface LarachimpListMember
{
	/**
	 * Return the first name of the subscriber to the Mailchimp list
	 * 
	 * @return string
	 */
    public function getFirstName();

    /**
     * Return the last name of the subscriber to the Mailchimp list
     * 
     * @return string
     */
    public function getLastName();

    /**
     * Returns the current email of the subscriber to the Mailchimp list
     * 
     * @return string
     */
    public function getEmail();

    /**
     * Returns true if the subscriber to the Mailchimp list wants to receive 
     * emails sent to the list members.
     * 
     * @return boolean
     */
    public function isSubscribedToMailchimpList();
}
