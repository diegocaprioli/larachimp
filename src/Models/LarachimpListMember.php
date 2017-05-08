<?php namespace DiegoCaprioli\Larachimp\Models;

interface LarachimpListMember
{
    public function getName();

    public function getEmail();

    public function isSubscribedToMailchimpList();
}
