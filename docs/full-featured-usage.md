# Full Featured Usage

Even though with the Basic Usage you can do anything that Mailchimp allows you to do, it's functionality is very simple and you must code almost everything yourself.

This package was created initially to hide the Guzzle configuration and handling while performing any request, but moreover, to provide a reusable set of features commonly used in our other projects. 

What we usually need to do in our web projects in Laravel, is to sync the data of the website users to a mailing list on a Mailchimp account. Usually the users of our websites have an option to "subscribe" or "unsubscribe" at any time from the website newsletter. Dealing with this logic every time, even when having the LarachimpFacade to help us, was repetitive and tediuos task. So we decided to include this feature in the Larachimp package, so we can reuse it's functionality among all the projects that require this same feature.

## Syncronize a User to your Mailchimp list

The package provides a simple way of of syncronizing a User to a Mailchimp list of your choosing.

1) Configure the Mailchimp list where you want the User details to be synced to (email). You can do this by completing the `list_id` in the `larachimp.php` config file.

2) In your Laravel app you will have a User class (or any other class representing a user for your applciation). You should make this class implement the interface [`LarachimpListMember`](../src/Models/LarachimpListMember.php). 
This interface will allow the Larachimp package to know and be able to get the details needed to sync your user to the Mailchimp list (basically it's name, email and wether the user wants to receive or not the newsletter).

3) In your Laravel app, whenever you need to syncronize the User to the Mailchimp list, you should dispatch a new [`SyncMailchimpMember`](../src/Jobs/SyncMailchimpMember.php) job to your queue, passing the User instance that needs to be synced up:

```php
$this->dispatch(new SyncMailchimpMember($user));
```

And that's it! The job will communicate with your Mailchimp account, and sync your user instance in the specidied list. If the user is not yet a subscriber of the Mailchimp list, it will be created. If the User instance returns `true` to the `isSubscribedToMailchimpList()` method required by the `LarachimpListMember` interface, it will set the subscriber as "Subscribed". On the other hand, if that method returns `false` it will set the subscriber as "Unsubscribed".

### Syncronizing directly, without a queued job

If for some reason you don't need or don't want to use a queued job, you can syncronize your user directly by interacting with the [`MailchimpManager`](../0.3/src/Services/MailchimpManager.php):

```php
$manager = App::make(MailchimpManager::class);
$manager->syncMember($user);
```

## Changing an existing user's email address

It is possible that a user in your applications changes it's email address. This is a special case that has to be dealt with appropiatelly. If you would call the syncronization process for this user again, the package would create a new member in the Mailchimp list for the email address (as the current changed email doesn't exist on the list yet). But the old email address will remain there.

Larachimp allows you to deal with this situation appropiatelly. You can dispatch a new `UpdateMailchimpMemberEmail` job for this:

```php
$this->dispatch(new UpdateMailchimpMemberEmail($user, $oldEmail));
```

The Mailchimp member address will be changed. Keep in mind that the user should already be returning the new and changed email address when performing this call (from the getEmail() method when implementing the `LarachimpListMember` interface).

### Changing the email directly, without a queued job

In the same way as with syncronizing, you will be able to directly change the email address in your Mailchimp list by interacting with the [`MailchimpManager`](../src/Services/MailchimpManager.php):

```php
$manager = App::make(MailchimpManager::class);
$manager->updateMembersEmail($user, $oldEmail);
```

## Removing a Mailchimp subscriber from the list

This package does not remove the email from the list when a user decides to not receive the newsletter anymore, but instead sets the status of the email to "Unsubscribed".

But in case that what the app needs it's to finally remove the email completelly from the Mailchimp list, it's also possible. Just dispatch a new `RemoveMailchimpMember` job:

```php
$this->dispatch(new RemoveMailchimpMember($email));
```

### Removing without a queued job

Following the same pattern as previous features, you will also be able to execute this functionality directly:

```php
$manager = App::make(MailchimpManager::class);
$manager->removeListMember($email);
```
