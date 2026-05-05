<!-- 
Frontend Contact/Message Form Example

This is an example of how to integrate the contact form on your frontend website.
The form will submit to the API endpoint: /api/messages/submit

Copy and modify this form in your frontend website/template.
-->

<form id="contactForm" class="contact-form">
    <div class="form-group mb-3">
        <label for="name" class="form-label">Your Name</label>
        <input type="text" class="form-control" id="name" name="name" required placeholder="Enter your full name">
        <small class="text-danger error-text" id="name-error"></small>
    </div>

    <div class="form-group mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
        <small class="text-danger error-text" id="email-error"></small>
    </div>

    <div class="form-group mb-3">
        <label for="phone" class="form-label">Phone (Optional)</label>
        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number">
    </div>

    <div class="form-group mb-3">
        <label for="subject" class="form-label">Subject</label>
        <input type="text" class="form-control" id="subject" name="subject" required placeholder="What is this about?">
        <small class="text-danger error-text" id="subject-error"></small>
    </div>

    <div class="form-group mb-3">
        <label for="message" class="form-label">Message</label>
        <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Type your message here..."></textarea>
        <small class="text-danger error-text" id="message-error"></small>
    </div>

    <button type="submit" class="btn btn-primary">Send Message</button>
    <small class="text-muted d-block mt-2">We'll respond to your message as soon as possible.</small>
</form>

<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value || null,
        subject: document.getElementById('subject').value,
        message: document.getElementById('message').value,
        origin_page: window.location.pathname // Track which page sent the message
    };

    try {
        const response = await fetch('/api/messages/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (response.ok) {
            // Clear form
            document.getElementById('contactForm').reset();
            // Show success message
            alert(data.message);
        } else {
            // Show validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errorElement = document.getElementById(field + '-error');
                    if (errorElement) {
                        errorElement.textContent = data.errors[field][0];
                    }
                });
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('There was an error sending your message. Please try again.');
    }
});
</script>
