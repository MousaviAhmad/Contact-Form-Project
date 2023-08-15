<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$sendgridAPIKey = $_ENV['SENDGRID_API_KEY']; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $validEmail = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$validEmail) {
        echo "Invalid email address.";
        exit;
    }
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    if ($name && $validEmail && $message) {
        $messageContent = "From: " . $validEmail . "\n\n" . $message;

        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom("contact@arsam.digital", "Arsam's Contact Form");
        $email->setSubject("New Contact Form Submission from $name");
        $email->addTo("ahmadmousavi9169@gmail.com", "Your Receiver Name");
        $email->addContent("text/plain", $messageContent);

        $sendgrid = new \SendGrid($sendgridAPIKey); 
 

        try {
            $response = $sendgrid->send($email);
            if ($response->statusCode() == 202) {  // 202 means email has been accepted for delivery
                // Send a confirmation email to the user
                $userEmail = new \SendGrid\Mail\Mail();
                $userEmail->setFrom("contact@arsam.digital", "Arsam's Contact Form");
                $userEmail->setSubject("Thank You for Contacting Us");
                $userEmail->addTo($validEmail, $name);
                $userEmail->addContent(
                    "text/plain",
                    "Dear $name,\n\nThank you for reaching out to us through our website's contact form. We appreciate your interest and will get back to you as soon as possible.\n\nBest regards,\nArsam"
                );
                $sendgrid->send($userEmail);
            } else {
                // This can give details about why sending failed. Remove in production.
                echo $response->body();  
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    } else {
        echo "Please fill in all fields correctly.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/output.css">
    <title>Contact Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 h-screen flex justify-center items-center">

<div class="bg-white p-8 rounded-lg shadow-md w-96" x-data="{ focusElement: null }">
        <h1 class="text-2xl font-semibold mb-4">Contact Us</h1>

        <form action="" method="post">
            <div class="mb-4 relative">
                <label for="name" class="block text-sm font-medium mb-2">Name:</label>
                <div x-on:input="focusElement = 'name'" :class="{'border-blue-400': focusElement === 'name'}" class="flex items-center p-2 border rounded-md transition duration-300">
                    <span class="mr-2"><svg viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-gray-500"><path d="M4 4a2 2 0 012-2h8a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path><path fill-rule="evenodd" d="M2 11a2 2 0 012-2h12a2 2 0 012 2v5a2 2 0 01-2 2H4a2 2 0 01-2-2v-5zm1 0v5a1 1 0 001 1h12a1 1 0 001-1v-5H3z" clip-rule="evenodd"></path></svg></span>
                    <input type="text" id="name" name="name" class="w-full bg-transparent focus:outline-none" required>
                </div>
            </div>

            <div class="mb-4 relative">
                <label for="email" class="block text-sm font-medium mb-2">Email:</label>
                <div x-on:input="focusElement = 'email'" :class="{'border-blue-400': focusElement === 'email'}" class="flex items-center p-2 border rounded-md transition duration-300">
                    <span class="mr-2"><svg viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-gray-500"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8l-8 4-8-4v6a2 2 0 002 2h12a2 2 0 002-2V8z"></path></svg></span>
                    <input type="email" id="email" name="email" class="w-full bg-transparent focus:outline-none" required>
                </div>
            </div>

            <div class="mb-4 relative">
                <label for="message" class="block text-sm font-medium mb-2">Message:</label>
                <div x-on:input="focusElement = 'message'" :class="{'border-blue-400': focusElement === 'message'}" class="p-2 border rounded-md transition duration-300">
                    <textarea id="message" name="message" rows="4" class="w-full bg-transparent focus:outline-none" required></textarea>
                </div>
            </div>

            <button type="submit" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium p-2 rounded-md w-full transition duration-300">Send Message</button>
        </form>
        <?php if(isset($name)) { ?>
        <div class="mt-4 p-2 border-l-4 border-green-500 bg-green-100 rounded-md">
            <svg viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6 text-green-500 inline-block">
                <path fill-rule="evenodd" d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <span class="ml-2 text-green-700">Thank you for contacting us, <?php echo $name; ?>. We will get back to you soon!</span>
        </div>
        <?php } ?>
    </div>

</body>
</html>
