@extends('layouts.vertical', ['title' => 'Write a Review'])

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Share Your Experience</h1>
            <p class="text-xl text-gray-600">Tell us about your moving experience with Kwikshift Movers</p>
        </div>

        <!-- Review Information -->
        <div class="grid md:grid-cols-2 gap-8 mb-12">
            <div class="bg-blue-50 p-8 rounded-lg">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Why Review Us?</h2>
                <ul class="space-y-3 text-gray-700">
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-3">✓</span>
                        <span>Help other customers make informed decisions</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-3">✓</span>
                        <span>Share your feedback with our team</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-3">✓</span>
                        <span>Your review helps us improve our services</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-3">✓</span>
                        <span>Build trust and transparency in our community</span>
                    </li>
                </ul>
            </div>

            <div class="bg-green-50 p-8 rounded-lg">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Leave a Review</h2>
                <p class="text-gray-700 mb-6">Share your experience on these trusted platforms:</p>
                <div class="space-y-3">
                    <a href="https://www.google.com/search?q=kwikshift+movers" target="_blank" rel="noopener noreferrer" class="block w-full bg-white border-2 border-gray-300 hover:border-blue-600 rounded-lg p-4 text-center font-semibold text-gray-700 hover:text-blue-600 transition">
                        Google Reviews
                    </a>
                    <a href="https://www.trustpilot.com" target="_blank" rel="noopener noreferrer" class="block w-full bg-white border-2 border-gray-300 hover:border-blue-600 rounded-lg p-4 text-center font-semibold text-gray-700 hover:text-blue-600 transition">
                        Trustpilot
                    </a>
                    <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" class="block w-full bg-white border-2 border-gray-300 hover:border-blue-600 rounded-lg p-4 text-center font-semibold text-gray-700 hover:text-blue-600 transition">
                        Facebook Reviews
                    </a>
                </div>
            </div>
        </div>

        <!-- Testimonials Section -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">What Our Customers Say</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                        </div>
                    </div>
                    <p class="text-gray-700 mb-4">"Professional, efficient, and affordable. Our move was completed on time and everything arrived in perfect condition."</p>
                    <p class="font-semibold text-gray-900">John Kamau</p>
                    <p class="text-sm text-gray-600">Residential Move - May 2026</p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                        </div>
                    </div>
                    <p class="text-gray-700 mb-4">"Excellent customer service! The team was courteous and handled our fragile items with care."</p>
                    <p class="font-semibold text-gray-900">Sarah Omondi</p>
                    <p class="text-sm text-gray-600">Office Relocation - April 2026</p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                        </div>
                    </div>
                    <p class="text-gray-700 mb-4">"Best moving company in Nairobi! Fair pricing, reliable service, and friendly staff. Highly recommended!"</p>
                    <p class="font-semibold text-gray-900">Peter Kipchoge</p>
                    <p class="text-sm text-gray-600">Long-Distance Move - March 2026</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-blue-600 text-white rounded-lg p-12 text-center">
            <h2 class="text-3xl font-bold mb-4">Had a Great Experience?</h2>
            <p class="text-lg mb-8">Your feedback is valuable. Share your review and help us continue to provide excellent moving services!</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="https://www.google.com/search?q=kwikshift+movers" target="_blank" rel="noopener noreferrer" class="bg-white text-blue-600 hover:bg-gray-100 font-bold py-3 px-8 rounded-lg transition">
                    Write a Google Review
                </a>
                <a href="mailto:feedback@kwikshiftmovers.co.ke" class="bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 px-8 rounded-lg transition">
                    Send Us Feedback
                </a>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">FAQ About Reviews</h2>
            <div class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">How long does it take for a review to appear?</h3>
                    <p class="text-gray-700">Most platforms typically display reviews within 24-48 hours after submission, though it may vary depending on their review moderation process.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Can I edit or delete my review?</h3>
                    <p class="text-gray-700">Yes, most review platforms allow you to edit or delete your review. Check the specific platform's guidelines for detailed instructions.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Is my review anonymous?</h3>
                    <p class="text-gray-700">Your review will be associated with your account on the review platform. However, you can use a pseudonym in some cases.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">What if I had a negative experience?</h3>
                    <p class="text-gray-700">We appreciate honest feedback. Please share your experience, and contact us directly at <a href="mailto:support@kwikshiftmovers.co.ke" class="text-blue-600 hover:underline">support@kwikshiftmovers.co.ke</a> so we can address your concerns.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
