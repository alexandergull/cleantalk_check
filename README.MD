# CleanTalkCheck

The `CleanTalkCheck` class is designed to interact with the CleanTalk API to check for spam and other unwanted content. It provides methods to set various parameters, send a request to the CleanTalk server, and retrieve a verdict.

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Troubleshooting](#troubleshooting)
- [Examples](#examples)
- [Contributing](#contributing)
- [Contact](#contact)


## Requirements

- PHP
- Composer
- JSON extension for PHP
- cURL extension for PHP
- Access key from CleanTalk Anti-Spam **paid** or **trial** account. If you do not have a key, [register the new account](https://cleantalk.org/register?utm_source=github&utm_medium=article&utm_campaign=instructions&utm_content=link&utm_term=create+account) or access dashboard to an [existing account](https://cleantalk.org/my?utm_source=github&utm_medium=referral&utm_campaign=bot_detector&utm_content=link&utm_term=dashboard) to get it.

## Installation
### Via direct download
1. Download the latest release from the [releases page](https://github.com/alexandergull/cleantalk_check/releases)
2. Extract the archive to your project directory.
3. Include the `src/autoload.php` file in your project in the file where you want to use the class.

### Via Composer
1. Add the required lib to your composer.json file:

   ```json
   {
      "require": {
          "alexandergull/cleantalk-check": "*"
      }
   }
   ```
2. The run composer install command:
    ```sh
    composer install
    ```

## Usage

- [Frontend Part](#frontend-part)
- [Backend Part](#backend-part)
- [Methods](#methods)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)

### Frontend part

1. Find the php file implements logic that generates HTML of your form and add the following code to the start of form :

   ```php
   <?php
   use CleanTalkCheck\CleanTalkCheck;
   require_once('../src/autoload.php'); // path of the autoload.php
   ?>
   ```

2. Then insert the code block to add the CleanTalk frontend scripts before the closing form tag:

   ```php
   ...
   <form type="post" action="form_handler.php">
   <-- Your form other fields -->
   <?php echo CleanTalkCheck::getFrontendHTMLCode()?>
   </form>
   ...
   ```

3. Final **frontend** PHP file code may look like the [example](example/contact_form_page.php).

4. If everything goes well, the HTML code of the CleanTalk bot detector will be added to the form. Make sure the tags are placed correctly checking the page opened in the web browser.
   ```html
   <form method="post" action="form_handler.php">
           <-- other form tags -->
           <input type="hidden" id="ct_form_start_time" name="ct_form_start_time" value="1742367202">
           <input type="hidden" id="ct_bot_detector_event_token_8400" name="ct_bot_detector_event_token" value="6174d180b4f5431cad16d5d2cc6044f7c0a5c88d757736647fa903d67ba32621">
   </form>
   ```

### Backend part

1. Find the form handler logic, the form from the example above sends data to `form_handler.php`.

2. Get the access key from the CleanTalk website. If you do not have a key, [register the new account](https://cleantalk.org/register?utm_source=github&utm_medium=article&utm_campaign=instructions&utm_content=link&utm_term=create+account) or access dashboard to an [existing account](https://cleantalk.org/my?utm_source=github&utm_medium=referral&utm_campaign=bot_detector&utm_content=link&utm_term=dashboard) to get it.

3. Use access key to initialize the `CleanTalkCheck` class. Set the required parameters and call the `getVerdict` method to get the verdict.

   ```php
   require_once '../src/autoload.php';
   
   $cleanTalkCheck = new \src\CleanTalkCheck\CleanTalkCheck('your_access_key');
   $verdict = $cleanTalkCheck
       ->setEventToken($_POST[$cleanTalkCheck::EVENT_TOKEN_FIELD_NAME])
       ->setFormStartTime($_POST[$cleanTalkCheck::FORM_START_TIME_FIELD_NAME])
       ->setIP() //if you have your own method to get IP address, pass the string of IP address to the method
       ->setEmail($_POST['email'])
       ->setNickName($_POST['username'])
       ->setMessage($_POST['message'])
       ->useContactFormCheck()
       ->setDoBlockNoJSVisitor()
       ->getVerdict();
   
   if (!$verdict->error && !$verdict->allowed) {
      die('Message blocked: ' . $verdict->comment);
   }
   
   die('Message sent');
   ```
Hint: if your form sends data via its own data container, use it instead of $_POST in appropriate methods.

5. The method `getVerdict` returns an object with the following properties:
    - `error`: A boolean indicating whether an error occurred.
    - `allowed`: A boolean indicating whether the message is allowed.
    - `comment`: A string containing the reason for blocking the message.
    - `request_link`: A string containing the CleanTalk request link. The request is available in your dashboard within 5 minutes after the request is sent.
6. Use the `$verdict->error` property to determine if CleanTalk check is completed correctly.
7. Use the `$verdict->allowed` property to determine whether to allow the message to be sent or perform any other logic you need.
8. The final backend code may look like the [example](example/form_handler.php).

That's all. The class is ready to use. 

You can read about method and testing features in the next sections.

### Methods
* `__construct($accessKey)`: Initializes the class with an access key.
* `getVerdict()`: Verifies data, sends a request to CleanTalk, and returns the verdict.
* `setEventToken($event_token = null)`: Sets the event token parameter. If the event token is not set, the filtration **hardly** loses its effectiveness.
* `setFormStartTime($form_start_time = null)`: Sets the form start time parameter. If the event token is not set, the filtration **hardly** loses its effectiveness.
* `setIP($ip = null)`: Sets the IP address parameter. If the IP address is not set, the class will try to get it from the request.
* `setEmail($email)`: Sets the email parameter.
* `setNickName($nickname)`: Sets the nickname parameter.
* `setMessage($message)`: Sets the message parameter. Recommended to use for contact forms.
* `setDoBlockNoJSVisitor()`: Enables blocking of visitors without JavaScript.
* * If visitor has no JS enabled, the required parameter `event_token` will not be set that causes loss filtration.
* * Due above, if called, the class checks if the visitor has JavaScript enabled and block the request if not.
* * If not called, the class does not check if the visitor has JavaScript enabled.
* `useRegistrationCheck()`: Sets the method to check new user registration. Applies the appropriate CleanTalk filtration ruleset.
* `useContactFormCheck()`: Sets the method to check contact form submissions. Applies the appropriate CleanTalk filtration ruleset.
* `getFrontendHTMLCode($warn_if_js_disabled = true)`: Returns HTML code to include the CleanTalk scripts and tags. Pass `true` if you want to warn visitor about JS disabled.
* `whatsWrong($return_as_json = false)`: Returns suggestions for improving the filtration quality.

### Testing

Anytime you call the `getVerdict` method, the class will collect all the data and possibly suggest set fo improvements.

You can call the `whatsWrong` method to get suggestions for improving the filtration quality.

Note, the method works correct only after the `getVerdict` method is called.

```php
$cleanTalkCheck->whatsWrong();
```

If the method called with no arguments, it returns suggestions via echo output.

If called with `true` argument, it returns a JSON string, then you can collect it the way you want.

The content is:
- Suggestion block, read the message and provide the required data
- Request data block, the data that was sent to the CleanTalk server
- Verdict block, the verdict of the request

```json
{
   "suggestions": {
      "average": [
         {
            "stack": "interface method ->setEmail() has not been called",
            "message": "Please, provide the email field content to improve check quality."
         },
         {
            "stack": "interface method ->setNickName() has not been called",
            "message": "Please, provide the nickname field content to improve check quality."
         },
         {
            "stack": "seen the call ->useContactFormCheck(), but interface method ->setMessage() has not been called",
            "message": "Please, provide the message field to improve check quality."
         }
      ]
   },
   "request_data": {
      "method_name": "check_message",
      "auth_key": "your_api_key",
      "message": null,
      "sender_nickname": null,
      "sender_email": null,
      "sender_ip": "127.0.0.1",
      "js_on": 1,
      "submit_time": 61,
      "event_token": "6174d180b4f5431cad16d5d2cc6044f7c0a5c88d757736647fa903d67ba32621",
      "agent": "php-cleantalk-check",
      "sender_info": "{\"REFFERRER\":\"http:\\\\/\\\\/localhost:63342\\\\/untitled2\\\\/example\\\\/contact_form_page.php\"}"
   },
   "verdict": {
      "allowed": 1,
      "comment": "*** Allowed. Anti-Spam by CleanTalk. ***",
      "error": "",
      "request_link": "https:\\/\\/cleantalk.org\\/my\\/show_requests?request_id=02c11e9c1da92535fa1e892e0ef4333b"
   }
}
```

### Troubleshooting
- Make sure that all the methods are called in the correct order, the method `getVerdict` should be called last.
- Make sure that the `getFrontendHTMLCode` method is called in the correct place in the form.
- Make sure that the CleanTalk service HTML tags is placed on the form correctly. 
- Make sure that the form handler file is receiving the data from the form correctly.

## Examples
Examples can be found in the [/example](example) folder.

## Contributing
Do not hesitate to contribute to the project. You can report bugs, suggest improvements, or add new features.

## Contact
If you have any questions or suggestions, please contact us via [email](mailto:support@cleantalk.org)
