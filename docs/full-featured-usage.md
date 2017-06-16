# Full Featured Usage

Even though with the Basic Usage you can do anything that Mailchimp allows you to do, it's functionality is very simple and you must code almost everything yourself.

This package was created initially to hide the Guzzle configuration and handling while performing any request, but moreover, to provide a reusable set of features commonly used in our other projects. 

What we usually need to do in our web projects in Laravel, is to sync the data of the website users to a mailing list on a Mailchimp account. Usually the users of our websites have an option to "subscribe" or "unsubscribe" at any time from the website newsletter. Dealing with this logic every time, even when having the LarachimpFacade to help us, was repetitive and tediuos task. So we decided to include this feature in the Larachimp package, so we can reuse it's functionality among all the projects that require this same feature.

## Syncronize a User to your Mailchimp list

The package provides a simple way of of syncronizing a User to a Mailchimp list of your choosing.

1) Configure the Mailchimp list where you want the User details to be synced to (email). You can do this by completing the `list_id` in the `larachimp.php` config file.

2) In your Laravel app you will have a User class (or any other class representing a user for your applciation). You should make this class implement the interface [`LarachimpListMember`](https://github.com/diegocaprioli/larachimp/blob/0.3/src/Models/LarachimpListMember.php). 
This interface will allow the Larachimp package to know and be able to get the details needed to sync your user to the Mailchimp list (basically it's name, email and wether the user wants to receive or not the newsletter).

3) In your Laravel app, whenever you need to syncronize the User to the Mailchimp list, you should dispatch a new [`SyncMailchimpMember`](https://github.com/diegocaprioli/larachimp/blob/0.3/src/Jobs/SyncMailchimpMember.php) job to your queue, passing the User instance that needs to be synced up:

```php
$this->dispatch(new SyncMailchimpMember($user));
```

And that's it! The job will communicate with your Mailchimp account, and sync your user instance in the specidied list. If the user is not yet a subscriber of the Mailchimp list, it will be created. If the User instance returns `true` to the `isSubscribedToMailchimpList()` method required by the `LarachimpListMember` interface, it will set the subscriber as "Subscribed". On the other hand, if that method returns `false` it will set the subscriber as "Unsubscribed".

### Syncronizing directly, without a queued job

If for some reason you don't need or don't want to use a queued job, you can syncronize your user directly by interacting with the [`MailchimpManager`](https://github.com/diegocaprioli/larachimp/blob/0.3/src/Services/MailchimpManager.php):

```php
$manager = App::make(MailchimpManager::class);
$manager->syncMember($this->member);
```
