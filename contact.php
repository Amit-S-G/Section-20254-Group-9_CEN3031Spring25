<?php
    include("header1.html")
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="styles/contact.css">
</head>
<body>
    <header class="contact-header">
        <h1>Contact Us</h1>
    </header>

    <section class="contact-container">
        <h2> ~ Get in Touch ~ </h2>
        <p class ="get-in-touch">If you have any questions, feedback, or suggestions, feel free to reach out.</p>

        <form action="https://api.web3forms.com/submit" method="POST" class="contact-form">
        <input type="hidden" name="access_key" value="478ce53c-c683-40be-9770-6b2624c9f9dd">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="5" placeholder="Type your message here..." required></textarea>

            <button type="submit" class="cta-button">Send Message</button>
        </form>
    </section>
</body>
</html>