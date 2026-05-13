@extends('layouts.vertical', ['title' => 'Help Center'])

@php
    $company = app(\App\Support\CompanyProfile::class)->data();
@endphp

@section('css')
<style>
    .help-page-header {
        background: var(--bs-primary);
        color: white;
        padding: 3rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    .help-page-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .help-page-header p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 0;
    }

    .help-search-container {
        margin-bottom: 2rem;
    }

    .help-search-input {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .help-search-input:focus {
        outline: none;
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.15);
    }

    .help-main-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2rem;
    }

    .help-nav-sidebar {
        position: sticky;
        top: 20px;
        height: fit-content;
    }

    .help-nav-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
    }

    .help-nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .help-nav-item {
        margin-bottom: 0.5rem;
    }

    .help-nav-link {
        display: block;
        padding: 10px 12px;
        color: #4b5563;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 0.95rem;
    }

    .help-nav-link:hover {
        background-color: #f3f4f6;
        color: var(--bs-primary);
    }

    .help-nav-link.active {
        background-color: #ede9fe;
        color: var(--bs-primary);
        font-weight: 600;
    }

    .help-content {
        padding: 0;
    }

    .help-section {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
    }

    .help-section h2 {
        font-size: 1.875rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #ede9fe;
        color: #1f2937;
    }

    .help-section h3 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        color: #374151;
    }

    .help-section h4 {
        font-size: 1rem;
        font-weight: 600;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        color: #4b5563;
    }

    .help-section p {
        margin-bottom: 1rem;
        line-height: 1.6;
        color: #4b5563;
    }

    .help-tip {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 1rem;
        border-radius: 0 6px 6px 0;
        margin: 1.5rem 0;
        font-size: 0.95rem;
    }

    .help-tip strong {
        color: #1d4ed8;
    }

    .help-warning {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        padding: 1rem;
        border-radius: 0 6px 6px 0;
        margin: 1.5rem 0;
        font-size: 0.95rem;
    }

    .help-warning strong {
        color: #d97706;
    }

    .help-important {
        background: #fef2f2;
        border-left: 4px solid #ef4444;
        padding: 1rem;
        border-radius: 0 6px 6px 0;
        margin: 1.5rem 0;
        font-size: 0.95rem;
    }

    .help-important strong {
        color: #dc2626;
    }

    .help-steps {
        margin: 1.5rem 0;
    }

    .help-step {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        align-items: flex-start;
    }

    .help-step-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        min-width: 32px;
        border-radius: 50%;
        background: var(--bs-primary);
        color: white;
        font-size: 0.875rem;
        font-weight: 700;
    }

    .help-step-content {
        padding-top: 4px;
    }

    .help-step-content p {
        margin: 0;
    }

    .help-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        font-size: 0.95rem;
    }

    .help-table thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .help-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
    }

    .help-table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #4b5563;
    }

    .help-table tbody tr:hover {
        background: #f9fafb;
    }

    .help-status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .help-no-results {
        text-align: center;
        padding: 3rem 2rem;
        color: #6b7280;
    }

    .help-no-results p {
        margin: 0;
    }

    ul, ol {
        margin-left: 1.5rem;
        margin-bottom: 1rem;
    }

    li {
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .help-main-layout {
            grid-template-columns: 1fr;
        }

        .help-nav-sidebar {
            position: static;
            display: none;
        }

        .help-page-header h1 {
            font-size: 1.875rem;
        }

        .help-page-header p {
            font-size: 1rem;
        }

        .help-section {
            padding: 1.5rem;
        }

        .help-section h2 {
            font-size: 1.5rem;
        }
    }

    @media print {
        .help-nav-sidebar {
            display: none;
        }

        #help-search {
            display: none;
        }

        .help-page-header {
            background: white;
            border: 1px solid #e5e7eb;
            color: #1f2937;
        }

        .help-page-header h1 {
            color: #1f2937;
        }

        .help-page-header p {
            color: #4b5563;
        }

        .help-section {
            page-break-inside: avoid;
            border: none;
            box-shadow: none;
            padding: 0;
            margin-bottom: 3rem;
        }

        .help-section h2 {
            page-break-before: always;
            border-bottom: 2px solid #e5e7eb;
        }

        a {
            color: inherit;
            text-decoration: none;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="help-page-header">
        <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <h1 style="margin-bottom: 0;">Help Center</h1>
                <p style="margin-bottom: 0; opacity: 0.95;">{{ $company['name'] ?? config('app.name') }} - Your complete guide to success</p>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="help-search-container">
        <input 
            type="text" 
            id="help-search"
            placeholder="Search help topics..."
            class="help-search-input"
        >
    </div>

    <!-- Main Layout: Sidebar + Content -->
    <div class="help-main-layout">
        <!-- Left: Sticky Navigation -->
        <nav class="help-nav-sidebar">
            <p class="help-nav-title">Contents</p>
            <ul class="help-nav-list">
                <li class="help-nav-item"><a href="#getting-started" class="help-nav-link">Getting Started</a></li>
                <li class="help-nav-item"><a href="#dashboard" class="help-nav-link">Dashboard</a></li>
                <li class="help-nav-item"><a href="#quote-requests" class="help-nav-link">Quote Requests</a></li>
                <li class="help-nav-item"><a href="#quotations" class="help-nav-link">Quotations</a></li>
                <li class="help-nav-item"><a href="#invoices" class="help-nav-link">Invoices</a></li>
                <li class="help-nav-item"><a href="#booking-timeline" class="help-nav-link">Booking Timeline</a></li>
                <li class="help-nav-item"><a href="#customers" class="help-nav-link">Customers</a></li>
                <li class="help-nav-item"><a href="#messages" class="help-nav-link">Messages</a></li>
                <li class="help-nav-item"><a href="#settings" class="help-nav-link">Settings</a></li>
                <li class="help-nav-item"><a href="#profile-security" class="help-nav-link">Profile & Security</a></li>
                <li class="help-nav-item"><a href="#email-notifications" class="help-nav-link">Email & Notifications</a></li>
                <li class="help-nav-item"><a href="#troubleshooting" class="help-nav-link">Troubleshooting</a></li>
            </ul>
        </nav>

        <!-- Right: Content Sections -->
        <div class="help-content">
            <!-- Section 1: Getting Started -->
            <section class="help-section" id="getting-started" data-help-section="getting-started">
                <h2>Getting Started</h2>

                <h3>What is {{ config('app.name') }}?</h3>
                <p>{{ config('app.name') }} is a comprehensive business management platform designed to streamline your sales operations. It helps you manage quote requests from customers, create professional quotations, issue invoices, track payments, and maintain detailed records of all business activities. The system is built to make the entire process from customer inquiry to final payment as efficient and transparent as possible.</p>

                <h3>How to Log In</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Visit {{ config('app.url') }}</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Enter your email address</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Enter your password</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Click "Login"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>If two-factor authentication is enabled: Check your email for a 6-digit code, enter it on the verification page. Codes expire in 10 minutes—click "Resend Code" if needed.</p>
                        </div>
                    </div>
                </div>

                <h3>First Steps After Logging In</h3>
                <ol>
                    <li><strong>Complete your profile:</strong> Add your name, job title, and signature (used on PDFs)</li>
                    <li><strong>Configure company settings:</strong> Set your company name, logo, address, and contact information</li>
                    <li><strong>Configure payment settings:</strong> Enable and set up M-Pesa, bank transfer, or cash payment options</li>
                    <li><strong>Configure SMTP email settings:</strong> Connect your email provider so the system can send automatic emails</li>
                    <li>You are ready to start using the system!</li>
                </ol>

                <h3>The Complete Workflow</h3>
                <p>Here's how the system guides customers through your business process:</p>
                <ol>
                    <li><strong>Quote Request:</strong> Customer submits a quote request from your website with their details and requirements</li>
                    <li><strong>Admin Approval:</strong> You review the incoming request and approve it to create a quotation</li>
                    <li><strong>Quotation:</strong> You create a detailed quotation with pricing, terms, and deposit requirements</li>
                    <li><strong>Send to Customer:</strong> The quotation is sent to the customer via email or WhatsApp with an approval link</li>
                    <li><strong>Customer Approval:</strong> The customer approves the quotation online or the admin approves it manually</li>
                    <li><strong>Deposit Collection:</strong> Mark when the required deposit is received (booking confirmation sent)</li>
                    <li><strong>Invoice Generation:</strong> Create an invoice based on the approved quotation</li>
                    <li><strong>Invoice Delivery:</strong> Send the invoice to the customer</li>
                    <li><strong>Payment Tracking:</strong> Mark the invoice as paid when payment is received</li>
                    <li><strong>Completion:</strong> Job complete with full audit trail and records</li>
                </ol>
            </section>

            <!-- Section 2: Dashboard -->
            <section class="help-section" id="dashboard" data-help-section="dashboard">
                <h2>Dashboard</h2>

                <p>The dashboard is your command center, showing key metrics and recent activity at a glance.</p>

                <h3>Dashboard Overview</h3>
                <p>When you log in, you'll see:</p>
                <ul>
                    <li><strong>Inquiry Stats:</strong> Number of new quote requests received today</li>
                    <li><strong>Quote Pipeline:</strong> Count of pending, approved, and declined quotations</li>
                    <li><strong>Revenue Metrics:</strong> Total quote amounts, projected deposits, and paid invoices</li>
                    <li><strong>Recent Activity:</strong> Latest quote requests, quotations sent, and payments received</li>
                    <li><strong>Charts and Trends:</strong> Visual representation of your sales pipeline and performance</li>
                </ul>

                <h3>Understanding Dashboard Metrics</h3>
                <ul>
                    <li><strong>Total Inquiries:</strong> All quote requests received in the selected period</li>
                    <li><strong>Pending:</strong> Quotations waiting for customer approval</li>
                    <li><strong>Approved:</strong> Quotations approved by customers, ready for invoicing</li>
                    <li><strong>Declined:</strong> Quotations rejected by customers or marked as rejected by you</li>
                    <li><strong>Projected Revenue:</strong> Sum of all approved quotation amounts</li>
                    <li><strong>Projected Deposits:</strong> Estimated deposit income based on quotation deposit percentages</li>
                    <li><strong>Email Delivery Status:</strong> Tracking of emails sent, opened, and failed</li>
                </ul>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> The dashboard updates in real-time as customers submit requests and approve quotes.</p>
                </div>
            </section>

            <!-- Section 3: Quote Requests -->
            <section class="help-section" id="quote-requests" data-help-section="quote-requests">
                <h2>Quote Requests</h2>

                <p>Quote requests are incoming inquiries from customers who want to do business with you. These come from your website form or can be manually created by your team.</p>

                <h3>What is a Quote Request?</h3>
                <p>A quote request contains the customer's initial inquiry with their contact information, service requirements, and preferred move date. It's the first step in your sales pipeline.</p>

                <h3>Fields in a Quote Request</h3>
                <ul>
                    <li><strong>Full Name:</strong> Customer's complete name</li>
                    <li><strong>Email:</strong> Customer's email address</li>
                    <li><strong>Phone:</strong> Customer's phone number</li>
                    <li><strong>Service Type:</strong> Type of service requested (moving, shipping, etc.)</li>
                    <li><strong>Move Size:</strong> Size or scope of the job</li>
                    <li><strong>Moving From:</strong> Origin location</li>
                    <li><strong>Moving To:</strong> Destination location</li>
                    <li><strong>Preferred Move Date:</strong> When the customer wants the service</li>
                    <li><strong>Contact Preference:</strong> Email, WhatsApp, or both</li>
                    <li><strong>Additional Notes:</strong> Any special requirements or details</li>
                    <li><strong>Source Page:</strong> Which page the inquiry came from</li>
                </ul>

                <h3>How to View Quote Requests</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Click "Quotes" in the sidebar menu</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>You'll see a list of all quote requests with the most recent first</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Click on a quote request to see its full details and take action</p>
                        </div>
                    </div>
                </div>

                <h3>Quote Request Statuses</h3>
                <table class="help-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Meaning</th>
                            <th>Next Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dbeafe; color: #0369a1;">New</span></td>
                            <td>Just received, not yet reviewed</td>
                            <td>Review and approve</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #e0e7ff; color: #4f46e5;">Processing</span></td>
                            <td>Being worked on, quotation is being prepared</td>
                            <td>Complete the quotation</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #fef3c7; color: #92400e;">Created</span></td>
                            <td>Quotation has been created</td>
                            <td>Send quotation to customer</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dbeafe; color: #0369a1;">Emailed</span></td>
                            <td>Quotation sent to customer</td>
                            <td>Await customer response</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dcfce7; color: #166534;">Approved</span></td>
                            <td>Customer approved the quotation</td>
                            <td>Create invoice and collect deposit</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #fee2e2; color: #991b1b;">Rejected</span></td>
                            <td>Customer declined or you rejected it</td>
                            <td>Follow up or move to next inquiry</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #fee2e2; color: #991b1b;">Spam</span></td>
                            <td>Marked as spam/junk</td>
                            <td>Archive or delete</td>
                        </tr>
                    </tbody>
                </table>

                <h3>How to Approve a Quote Request</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Open the quote request you want to approve</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Review the customer's details and requirements</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Click "Approve" or "Create Quotation"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>You'll be taken to create a detailed quotation</p>
                        </div>
                    </div>
                </div>

                <h3>How to Reject a Quote Request</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Open the quote request</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Reject" button</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>The status changes to "Rejected"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>You can optionally send a rejection email to the customer</p>
                        </div>
                    </div>
                </div>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> Customers who submit requests appear in your Customers section so you can track repeat inquiries.</p>
                </div>
            </section>

            <!-- Section 4: Quotations -->
            <section class="help-section" id="quotations" data-help-section="quotations">
                <h2>Quotations</h2>

                <p>A quotation is a formal document you create for an approved quote request. It includes itemized services, pricing, deposit requirements, terms, and conditions. Customers approve the quotation before you invoice them.</p>

                <h3>What is a Quotation?</h3>
                <p>A quotation is your professional offer to a customer. It details what you'll provide, how much it costs, when they need to pay the deposit, what the payment terms are, and your cancellation policy. Once the customer approves it, you proceed to create an invoice.</p>

                <h3>Creating a Quotation</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Approve a quote request</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>You'll be taken to the quotation form</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Fill in the quotation details (auto-filled from quote request)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Add line items describing services and pricing</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>Set quote amount and deposit percentage</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">6</div>
                        <div class="help-step-content">
                            <p>Add payment terms and cancellation policy</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">7</div>
                        <div class="help-step-content">
                            <p>Set quote valid until date (default 7 days)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">8</div>
                        <div class="help-step-content">
                            <p>Click "Save as Draft" or "Save & Send"</p>
                        </div>
                    </div>
                </div>

                <h3>Quotation Fields</h3>
                <ul>
                    <li><strong>Customer Details:</strong> Auto-filled from quote request (name, email, phone)</li>
                    <li><strong>Moving From/To:</strong> Origin and destination locations</li>
                    <li><strong>Move Date:</strong> Scheduled service date</li>
                    <li><strong>Quote Date:</strong> Date quotation was created</li>
                    <li><strong>Quote Valid Until:</strong> Expiration date for this quote (default 7 days)</li>
                    <li><strong>Quote Amount:</strong> Total price for the service</li>
                    <li><strong>Deposit Percentage:</strong> Percent of quote amount required as deposit (e.g., 30%)</li>
                    <li><strong>Payment Terms:</strong> Description of payment schedule and methods accepted</li>
                    <li><strong>Cancellation Policy:</strong> Cancellation notice period and any fees</li>
                    <li><strong>Services Included:</strong> List of what's included in the quote</li>
                    <li><strong>Additional Notes:</strong> Any special terms or information</li>
                </ul>

                <h3>Pricing Calculation</h3>
                <ul>
                    <li><strong>Quote Amount:</strong> Total price you set</li>
                    <li><strong>Deposit Amount:</strong> Automatically calculated as (Quote Amount × Deposit Percentage ÷ 100)</li>
                    <li><strong>Balance Due:</strong> Quote Amount minus Deposit Amount</li>
                </ul>

                <h3>How to Preview the PDF</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Open the quotation</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Download PDF" or "Preview PDF"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Review how the PDF will look when sent to customer</p>
                        </div>
                    </div>
                </div>

                <h3>How to Send a Quotation</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Open the quotation (status must be "Draft")</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Send" button</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Choose delivery method: Email or WhatsApp</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>If Email: The quotation PDF is attached and sent automatically with an approval link</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>If WhatsApp: You get a wa.me link to share via WhatsApp (approval link included)</p>
                        </div>
                    </div>
                </div>

                <h3>Quotation Statuses</h3>
                <table class="help-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Meaning</th>
                            <th>Available Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="help-status-badge" style="background: #f3e8ff; color: #6b21a8;">Draft</span></td>
                            <td>Being prepared, not yet sent</td>
                            <td>Edit, Send, Download PDF</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dbeafe; color: #0369a1;">Sent</span></td>
                            <td>Delivered to customer, awaiting response</td>
                            <td>Resend, Approve manually, Decline, View tracking</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dcfce7; color: #166534;">Approved</span></td>
                            <td>Customer approved, ready to invoice</td>
                            <td>Create invoice, Download PDF, Mark deposit received</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #fee2e2; color: #991b1b;">Declined</span></td>
                            <td>Customer rejected or you rejected it</td>
                            <td>View only, Duplicate as new quote</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Approval Process</h3>
                <p>When you send a quotation to the customer via email, they receive an "Approve" link. They can:</p>
                <ol>
                    <li>Click the link (expires after 7 days)</li>
                    <li>Review the quotation PDF</li>
                    <li>Enter their name and confirm they agree</li>
                    <li>Submit their approval</li>
                </ol>

                <p>The system automatically sends them a thank-you email and moves the quotation to "Approved" status.</p>

                <h3>Manual Approval</h3>
                <p>If the customer approves verbally or another way, you can manually approve:</p>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Open the quotation (status "Sent")</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Approve" button</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Status changes to "Approved"</p>
                        </div>
                    </div>
                </div>

                <h3>Marking Deposit as Received</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Open an approved quotation</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Mark Deposit Received" button</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Enter deposit amount, reference (M-Pesa code, check number, etc.), and payment method</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Click "Confirm"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>Customer receives "Booking Confirmed" email, quotation timeline updated</p>
                        </div>
                    </div>
                </div>

                <h3>Booking Timeline</h3>
                <p>On the quotation details page, you'll see a timeline showing all major events:</p>
                <ul>
                    <li>When quotation was created</li>
                    <li>When it was sent (and via which channel)</li>
                    <li>When customer clicked the approval link</li>
                    <li>When customer opened the email</li>
                    <li>When it was approved</li>
                    <li>When deposit was received</li>
                    <li>When booking was confirmed</li>
                </ul>

                <h3>Authorization Section</h3>
                <p>Every quotation PDF includes an authorization section with a signature from your profile. This shows who authorized the quotation and validates it professionally.</p>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> The approval link in the quotation email expires after 7 days. If the link expires, go to the quotation and click "Resend" to generate a new link.</p>
                </div>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> Your signature must be uploaded in Profile Settings for it to appear on quotations. Go to Account → Signature to upload an image.</p>
                </div>

                <div class="help-warning">
                    <p><strong><i data-lucide="triangle-alert" class="icon-xs me-1"></i>Note:</strong> Once a quotation is approved, you cannot edit it. This protects the record integrity. If changes are needed, you can duplicate it as a new quotation.</p>
                </div>
            </section>

            <!-- Section 5: Invoices -->
            <section class="help-section" id="invoices" data-help-section="invoices">
                <h2>Invoices</h2>

                <p>An invoice is the formal billing document you send to a customer after they approve a quotation. It requests full payment and can be marked as paid when money is received.</p>

                <h3>What is an Invoice?</h3>
                <p>An invoice is a legal document that requests payment from a customer. It's created from an approved quotation and shows itemized services, pricing, payment due date, and accepted payment methods. It becomes the official record of the financial transaction.</p>

                <h3>Creating an Invoice</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to Invoices → Create Invoice</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Select the approved quotation (auto-fills all details)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Set invoice date and due date</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Line items auto-fill from quotation (edit if needed)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>Review deposit paid and balance due amounts</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">6</div>
                        <div class="help-step-content">
                            <p>Add any notes or special terms</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">7</div>
                        <div class="help-step-content">
                            <p>Click "Save as Draft" or "Save & Send"</p>
                        </div>
                    </div>
                </div>

                <h3>What Auto-fills from Quotation</h3>
                <ul>
                    <li>Customer name, email, phone</li>
                    <li>Moving from/to locations</li>
                    <li>Move date</li>
                    <li>Line items and pricing</li>
                    <li>Deposit already paid (if marked in quotation)</li>
                    <li>Balance due calculation</li>
                </ul>

                <h3>Invoice Statuses</h3>
                <table class="help-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Meaning</th>
                            <th>Available Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="help-status-badge" style="background: #f3e8ff; color: #6b21a8;">Draft</span></td>
                            <td>Being prepared, not yet sent</td>
                            <td>Edit, Send, Download PDF</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dbeafe; color: #0369a1;">Sent</span></td>
                            <td>Delivered to customer</td>
                            <td>Resend, Mark as paid, Download PDF</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dcfce7; color: #166534;">Paid</span></td>
                            <td>Payment received and recorded</td>
                            <td>Download PDF, View only</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #fee2e2; color: #991b1b;">Overdue</span></td>
                            <td>Past due date, payment not received</td>
                            <td>Send reminder, Mark as paid, Mark as void</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #fee2e2; color: #991b1b;">Void</span></td>
                            <td>Cancelled, not valid</td>
                            <td>View only</td>
                        </tr>
                    </tbody>
                </table>

                <h3>How to Send an Invoice</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Open the invoice (status must be "Draft" or "Sent")</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Send" button</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Choose: Email or WhatsApp</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>If Email: Invoice PDF is attached, payment instructions included</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>If WhatsApp: You get a wa.me link with invoice preview</p>
                        </div>
                    </div>
                </div>

                <h3>How to Mark as Paid</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Open the invoice</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Mark as Paid" button</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Status changes to "Paid"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Customer receives "Payment Received" confirmation email</p>
                        </div>
                    </div>
                </div>

                <h3>Payment Information</h3>
                <p>The invoice automatically includes payment methods configured in Settings. This shows customers exactly how to pay you:</p>
                <ul>
                    <li><strong>M-Pesa:</strong> Till number, paybill, or Pochi la Biashara details</li>
                    <li><strong>Bank Transfer:</strong> Bank name, account number, branch, Swift code</li>
                    <li><strong>Cash:</strong> Special instructions if applicable</li>
                </ul>

                <h3>Email Delivery Tracking</h3>
                <p>On the invoice page, you can see email delivery status:</p>
                <ul>
                    <li><strong><i data-lucide="check-circle-2" class="icon-xs me-1"></i>Sent:</strong> Email was sent successfully</li>
                    <li><strong>👁 Opened:</strong> Customer opened the email (tracking pixel)</li>
                    <li><strong><i data-lucide="x-circle" class="icon-xs me-1"></i>Failed:</strong> Email failed to deliver (shows error)</li>
                </ul>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> Invoice auto-fills from the approved quote, saving you time.</p>
                </div>

                <div class="help-warning">
                    <p><strong><i data-lucide="triangle-alert" class="icon-xs me-1"></i>Note:</strong> Once an invoice is marked as Paid, you cannot edit the line items.</p>
                </div>

                <div class="help-important">
                    <p><strong>🚨 Important:</strong> Make sure payment settings are configured first so payment information appears on invoices.</p>
                </div>
            </section>

            <!-- Section 6: Booking Timeline -->
            <section class="help-section" id="booking-timeline" data-help-section="booking-timeline">
                <h2>Booking Timeline</h2>

                <p>The booking timeline is a chronological record of all events for a quotation or invoice, providing complete transparency and audit trail.</p>

                <h3>What is the Booking Timeline?</h3>
                <p>The timeline shows every significant action taken on a quotation or invoice, who took it, when it happened, and how it happened (email, online, admin action, etc.). It's your complete audit trail.</p>

                <h3>Where to Find It</h3>
                <ul>
                    <li>On the Quotation Details page (scroll to bottom)</li>
                    <li>On the Invoice Details page (scroll to bottom)</li>
                </ul>

                <h3>Timeline Events</h3>
                <p>The system tracks these events automatically:</p>

                <h4>Quote Request Events:</h4>
                <ul>
                    <li><strong>REQUEST_SUBMITTED:</strong> New quote request received (green checkmark)</li>
                </ul>

                <h4>Quotation Events:</h4>
                <ul>
                    <li><strong>QUOTE_CREATED:</strong> Quotation created by admin</li>
                    <li><strong>QUOTE_UPDATED:</strong> Quotation details edited</li>
                    <li><strong>QUOTE_SENT:</strong> Sent to customer via email or WhatsApp</li>
                    <li><strong>APPROVAL_LINK_CLICKED:</strong> Customer clicked approval link</li>
                    <li><strong>EMAIL_OPENED:</strong> Customer opened the quotation email</li>
                    <li><strong>APPROVED_ONLINE:</strong> Customer approved quotation online</li>
                    <li><strong>PDF_DOWNLOADED:</strong> Quotation PDF was downloaded</li>
                    <li><strong>DEPOSIT_RECEIVED:</strong> Deposit payment recorded</li>
                    <li><strong>BOOKING_CONFIRMED:</strong> Booking confirmed after deposit</li>
                    <li><strong>REJECTED:</strong> Quotation was rejected</li>
                </ul>

                <h4>Invoice Events:</h4>
                <ul>
                    <li><strong>INVOICE_SENT:</strong> Invoice sent to customer</li>
                    <li><strong>PDF_DOWNLOADED:</strong> Invoice PDF was downloaded</li>
                    <li><strong>PAYMENT_RECEIVED:</strong> Invoice marked as paid</li>
                    <li><strong>EMAIL_OPENED:</strong> Customer opened invoice email</li>
                </ul>

                <h3>Timeline Icon Colors</h3>
                <table class="help-table">
                    <thead>
                        <tr>
                            <th>Color</th>
                            <th>Icon</th>
                            <th>Meaning</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dcfce7; color: #166534;">Green</span></td>
                            <td>✓ Check</td>
                            <td>Completed successfully (approved, confirmed, paid)</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dbeafe; color: #0369a1;">Blue</span></td>
                            <td>👁 Eye</td>
                            <td>Opened or viewed by customer</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #fee2e2; color: #991b1b;">Red</span></td>
                            <td>✗ X</td>
                            <td>Rejected or failed</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #f3f4f6; color: #4b5563;">Gray</span></td>
                            <td>⏱ Clock</td>
                            <td>Pending or in progress</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Information Shown</h3>
                <p>Each timeline entry shows:</p>
                <ul>
                    <li><strong>Description:</strong> What happened</li>
                    <li><strong>Date & Time:</strong> Exactly when it occurred</li>
                    <li><strong>Who:</strong> Customer name or admin name</li>
                    <li><strong>How:</strong> Via email, online, WhatsApp, system, or admin action</li>
                </ul>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> Use the timeline to troubleshoot issues—see exactly when things happened and what actions were taken.</p>
                </div>
            </section>

            <!-- Section 7: Customers -->
            <section class="help-section" id="customers" data-help-section="customers">
                <h2>Customers</h2>

                <p>The Customers section lets you view, manage, and track all your customers in one place. Your customer database is automatically updated as quote requests come in.</p>

                <h3>What is the Customers Section?</h3>
                <p>It's a unified view of everyone who has requested quotes or done business with you. You can see their contact info, service history, quotes, and invoices all in one place.</p>

                <h3>Customer Statuses</h3>
                <table class="help-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Meaning</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dbeafe; color: #0369a1;">Lead</span></td>
                            <td>Submitted a quote request but hasn't approved a quote yet</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #dcfce7; color: #166534;">Active Client</span></td>
                            <td>Has approved a quotation or has an active booking</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #fef3c7; color: #92400e;">Completed</span></td>
                            <td>Completed a booking and invoice is paid</td>
                        </tr>
                        <tr>
                            <td><span class="help-status-badge" style="background: #f3f4f6; color: #4b5563;">Inactive</span></td>
                            <td>No activity in the last 6 months</td>
                        </tr>
                    </tbody>
                </table>

                <h3>How to Add a New Customer</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to Customers → Add Customer</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Enter full name, email, and phone</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Optionally set status (Lead, Active Client, Completed, Inactive)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Click "Add Customer"</p>
                        </div>
                    </div>
                </div>

                <h3>Customer History</h3>
                <p>On a customer's profile you can see:</p>
                <ul>
                    <li><strong>Contact Information:</strong> Name, email, phone</li>
                    <li><strong>Service History:</strong> Latest service type and route</li>
                    <li><strong>Statistics:</strong> Total quotes sent, approved, declined</li>
                    <li><strong>Recent Activity:</strong> Quotes, quotations, and invoices</li>
                    <li><strong>Customer Status:</strong> Lead, Active Client, Completed, Inactive</li>
                </ul>

                <h3>How to Edit a Customer</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Click on the customer name</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Edit" button</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Update name, email, or phone</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Click "Save"</p>
                        </div>
                    </div>
                </div>

                <h3>How to Search/Filter Customers</h3>
                <ul>
                    <li>Use the search bar to find by name, email, or phone</li>
                    <li>Filter by customer status (Lead, Active Client, etc.)</li>
                    <li>View all customers or search for specific ones</li>
                </ul>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> Customers are automatically created when quote requests are submitted. You can also manually add customers.</p>
                </div>
            </section>

            <!-- Section 8: Messages -->
            <section class="help-section" id="messages" data-help-section="messages">
                <h2>Messages</h2>

                <p>The Messages section lets you compose emails, receive messages from customers and prospects, and track all communication in one place.</p>

                <h3>What are Messages?</h3>
                <p>Messages are direct communications with customers, prospects, and other contacts. You can send bulk emails, respond to inquiries, and maintain complete email history.</p>

                <h3>How to Compose a New Email</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to Messages → Compose</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Enter recipient email address</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Enter email subject line</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Type the email body message</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>Optionally attach a file (PDF, image, document)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">6</div>
                        <div class="help-step-content">
                            <p>Choose sender role (Sales, Support, Careers, Other)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">7</div>
                        <div class="help-step-content">
                            <p>Click "Send"</p>
                        </div>
                    </div>
                </div>

                <h3>Message Compose Fields</h3>
                <ul>
                    <li><strong>To (Email):</strong> Recipient's email address</li>
                    <li><strong>Subject:</strong> Email subject line (required)</li>
                    <li><strong>Message:</strong> Email body text (required)</li>
                    <li><strong>Attachment:</strong> Optional file to include (max 10MB)</li>
                    <li><strong>Sender Role:</strong> Which department this comes from</li>
                </ul>

                <h3>How to Read a Received Message</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to Messages → Inbox</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click on a message to open it</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Message is automatically marked as read</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>View sender details, date, time, and any attachment</p>
                        </div>
                    </div>
                </div>

                <h3>How to Reply to a Message</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Open the received message</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Reply" button</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Type your response</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Click "Send Reply"</p>
                        </div>
                    </div>
                </div>

                <h3>Email Delivery Status</h3>
                <p>Every sent message shows a delivery status:</p>
                <ul>
                    <li><strong><i data-lucide="check-circle-2" class="icon-xs me-1"></i>Sent:</strong> Email delivered successfully</li>
                    <li><strong>👁 Opened:</strong> Recipient has opened the email</li>
                    <li><strong><i data-lucide="x-circle" class="icon-xs me-1"></i>Failed:</strong> Email could not be delivered (shows error reason)</li>
                </ul>

                <h3>If an Email Fails</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to Messages and find the failed message</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Retry" to send again</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>If still failing, check email settings or contact support</p>
                        </div>
                    </div>
                </div>

                <h3>Email Log / Delivery Report</h3>
                <p>Go to Reports → Email Delivery to see:</p>
                <ul>
                    <li>All emails sent from the system</li>
                    <li>Delivery status (sent, opened, failed)</li>
                    <li>When each email was sent and opened</li>
                    <li>Error messages if delivery failed</li>
                </ul>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> When you send a message, it's logged in the system for your records and the customer's email is added to your contacts.</p>
                </div>
            </section>

            <!-- Section 9: Settings -->
            <section class="help-section" id="settings" data-help-section="settings">
                <h2>Settings</h2>

                <p>Settings is where you configure how your business operates in the system. Everything you set here affects how invoices look, what payment options customers see, and how emails are sent.</p>

                <h3>Company Settings</h3>
                <h4>Where to find them:</h4>
                <p>Settings → Company Information</p>

                <h4>What to configure:</h4>
                <ul>
                    <li><strong>Company Name:</strong> Your business name (appears on all PDFs)</li>
                    <li><strong>Company Email:</strong> Primary business email</li>
                    <li><strong>Company Phone:</strong> Business phone number</li>
                    <li><strong>Company Address:</strong> Full business address</li>
                    <li><strong>Company Website:</strong> Your website URL</li>
                    <li><strong>Company Logo:</strong> Upload your business logo (appears on PDFs)</li>
                </ul>

                <h4>How to update:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to Settings → Company Information</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Edit the fields you need to update</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Click "Save"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Changes appear immediately on all new PDFs</p>
                        </div>
                    </div>
                </div>

                <h3>Payment Settings</h3>
                <h4>Where to find them:</h4>
                <p>Settings → Payment Options</p>

                <h4>What to configure:</h4>
                <p>You can enable and configure payment methods that will appear on invoices:</p>

                <h4>M-Pesa (Kenya):</h4>
                <p>Choose your M-Pesa account type:</p>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p><strong>Till Number:</strong> If you have a Till/Shop account, enter the till number and account name</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p><strong>Paybill:</strong> If you have a Business Paybill account, enter business number, account number, and account name</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p><strong>Pochi la Biashara:</strong> If you have a Pochi account, enter the registered phone number and account name</p>
                        </div>
                    </div>
                </div>

                <h4>Bank Transfer:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Enable bank transfer option (toggle on)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Enter bank name</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Enter account holder name</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Enter account number</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>Enter branch name</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">6</div>
                        <div class="help-step-content">
                            <p>Optionally enter SWIFT code for international transfers</p>
                        </div>
                    </div>
                </div>

                <h4>Cash Payment:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Enable cash option (toggle on)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Enter special instructions if any (e.g., "Payment due before service")</p>
                        </div>
                    </div>
                </div>

                <h3>Email / SMTP Settings</h3>
                <h4>Where to find them:</h4>
                <p>Settings → Email Configuration</p>

                <h4>What is SMTP?</h4>
                <p>SMTP (Simple Mail Transfer Protocol) is the system that allows the application to send emails on your behalf. You need to configure it so the system can send automatic emails to customers.</p>

                <h4>How to configure Gmail SMTP:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Enable 2-Step Verification on your Google account (sign in to accounts.google.com)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Go to Google Account → Security → App Passwords</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Generate an app-specific password (Google will give you 16 characters)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>In {{ config('app.name') }} → Settings → Email</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>Set SMTP Host: smtp.gmail.com</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">6</div>
                        <div class="help-step-content">
                            <p>Set SMTP Port: 587</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">7</div>
                        <div class="help-step-content">
                            <p>SMTP Username: your Gmail address</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">8</div>
                        <div class="help-step-content">
                            <p>SMTP Password: The 16-character app password (NOT your Gmail login password)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">9</div>
                        <div class="help-step-content">
                            <p>Click "Save & Test" to verify it works</p>
                        </div>
                    </div>
                </div>

                <h4>Email Addresses Per Purpose:</h4>
                <p>Configure which email address sends each type of notification:</p>
                <ul>
                    <li><strong>Invoices & Quotes:</strong> Use your sales@yourdomain.com or main business email</li>
                    <li><strong>General Messages:</strong> Use info@yourdomain.com</li>
                    <li><strong>System/Auto Emails:</strong> Use noreply@yourdomain.com</li>
                    <li><strong>Job Postings:</strong> Use careers@yourdomain.com</li>
                </ul>

                <h3>Invoice Settings</h3>
                <h4>Where to find them:</h4>
                <p>Settings → Invoice Defaults</p>

                <h4>What to configure:</h4>
                <ul>
                    <li><strong>Default Payment Terms:</strong> What you show on every new invoice (e.g., "50% deposit required...")</li>
                    <li><strong>Default Cancellation Policy:</strong> Your standard cancellation terms</li>
                    <li><strong>Thank You Message:</strong> Personal message that appears on invoices</li>
                </ul>

                <h4>Available Placeholders:</h4>
                <p>In the thank you message, you can use:</p>
                <ul>
                    <li>{company_name} - Your company name</li>
                    <li>{company_email} - Your business email</li>
                    <li>{company_phone} - Your business phone</li>
                </ul>

                <h3>Example Thank You Message:</h3>
                <p>"Thank you for choosing {company_name}! For any questions, contact us at {company_email} or {company_phone}."</p>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> Test your email settings before sending quotes or invoices. Click "Send Test Email" to verify everything works.</p>
                </div>
            </section>

            <!-- Section 10: Profile & Security -->
            <section class="help-section" id="profile-security" data-help-section="profile-security">
                <h2>Profile & Security</h2>

                <p>Your profile is where you manage your personal information, upload your signature, and control security settings.</p>

                <h3>Profile Settings</h3>

                <h4>How to Update Your Name and Job Title:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to My Account</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click the "Profile" tab</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Update name, email, phone, job title, company, location, or bio</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Click "Save"</p>
                        </div>
                    </div>
                </div>

                <h4>How to Upload Your Signature:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to My Account → Profile tab</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Scroll to "Your Signature" section</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Choose to Upload or Draw your signature</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p><strong>If uploading:</strong> Select a PNG or JPG image file from your computer</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p><strong>If drawing:</strong> Use your mouse or trackpad to draw your signature</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">6</div>
                        <div class="help-step-content">
                            <p>Click "Save"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">7</div>
                        <div class="help-step-content">
                            <p>Your signature now appears on all quotations and invoices</p>
                        </div>
                    </div>
                </div>

                <h4>Signature Image Tips:</h4>
                <ul>
                    <li>Use PNG or JPG format</li>
                    <li>Keep the background white or transparent</li>
                    <li>Make sure the signature is clear and legible</li>
                    <li>File size should be under 2MB</li>
                    <li>Recommended size: 400x150 pixels</li>
                </ul>

                <h4>What happens if no signature is uploaded?</h4>
                <p>Quotations and invoices will show "Signature not available" in the authorization section. Customers and partners may view this as less professional, so it's recommended to always upload your signature.</p>

                <h3>Security Settings</h3>

                <h4>How to Change Your Password:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to My Account</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click the "Security" tab</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Enter your current password</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Enter your new password (must be 8+ characters, include uppercase, lowercase, and numbers)</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>Confirm the new password</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">6</div>
                        <div class="help-step-content">
                            <p>Click "Update Password"</p>
                        </div>
                    </div>
                </div>

                <h4>Password Requirements:</h4>
                <ul>
                    <li>Minimum 8 characters</li>
                    <li>Must include uppercase letters (A-Z)</li>
                    <li>Must include lowercase letters (a-z)</li>
                    <li>Must include numbers (0-9)</li>
                    <li>Example: NewPass123</li>
                </ul>

                <h4>How to Enable Two-Factor Authentication (2FA):</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to My Account → Security tab</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Find "Two-Factor Authentication" section</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Click "Enable Two-Factor Authentication"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>A 6-digit code will be sent to your email address</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>Enter the 6-digit code to confirm</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">6</div>
                        <div class="help-step-content">
                            <p>2FA is now enabled! On next login, you'll need to enter a code from your email</p>
                        </div>
                    </div>
                </div>

                <h4>How Two-Factor Authentication Works:</h4>
                <ol>
                    <li>You enter your email and password as usual</li>
                    <li>System sends a 6-digit code to your email</li>
                    <li>You enter this code on the verification page</li>
                    <li>Code is only valid for 10 minutes</li>
                    <li>You must request a new code if the time expires</li>
                    <li>You're logged in after code verification</li>
                </ol>

                <h4>How to Disable Two-Factor Authentication:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to My Account → Security tab</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Click "Disable Two-Factor Authentication"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Confirm you want to disable it</p>
                        </div>
                    </div>
                </div>

                <h4>Last Login Information:</h4>
                <p>Your Account page shows your last login date, time, and IP address for security tracking.</p>

                <h4>How to Log Out All Other Devices:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to My Account → Security tab</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Find "Active Sessions" section</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Click "Log Out All Other Devices"</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>All sessions on other devices/browsers are ended</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">5</div>
                        <div class="help-step-content">
                            <p>Only your current session remains active</p>
                        </div>
                    </div>
                </div>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> Use two-factor authentication for added security. It only takes a few seconds and greatly protects your account.</p>
                </div>
            </section>

            <!-- Section 11: Email & Notifications -->
            <section class="help-section" id="email-notifications" data-help-section="email-notifications">
                <h2>Email & Notifications</h2>

                <p>The system automatically sends emails at key points in your workflow. Here's what happens and when.</p>

                <h3>Automatic Emails Sent</h3>

                <h4>When a Quote Request is Received:</h4>
                <ul>
                    <li><strong>You receive:</strong> Admin notification email with quote request details</li>
                    <li><strong>Customer receives:</strong> Confirmation that their request was received</li>
                    <li><strong>Subject:</strong> "We received your quote request"</li>
                </ul>

                <h4>When You Approve a Quote Request:</h4>
                <ul>
                    <li><strong>Customer receives:</strong> Email notification (optional, you control this)</li>
                    <li><strong>Status changes:</strong> Quote request marked as "Processing"</li>
                </ul>

                <h4>When You Send a Quotation:</h4>
                <ul>
                    <li><strong>Customer receives:</strong> Email with quotation PDF attached and approval link</li>
                    <li><strong>Subject:</strong> "Here's your quotation"</li>
                    <li><strong>Includes:</strong> Detailed quote, payment terms, approval link</li>
                    <li><strong>Approval Link:</strong> Expires in 7 days</li>
                </ul>

                <h4>When Customer Approves Online:</h4>
                <ul>
                    <li><strong>You receive:</strong> Admin notification of approval</li>
                    <li><strong>Customer receives:</strong> "Booking confirmed" thank-you email</li>
                    <li><strong>Quotation status:</strong> Changes to "Approved"</li>
                </ul>

                <h4>When Deposit is Marked Received:</h4>
                <ul>
                    <li><strong>You receive:</strong> Admin notification</li>
                    <li><strong>Customer receives:</strong> "Deposit received - Booking confirmed" email</li>
                    <li><strong>Timeline:</strong> Updated with deposit details</li>
                </ul>

                <h4>When You Send an Invoice:</h4>
                <ul>
                    <li><strong>Customer receives:</strong> Invoice email with PDF attached</li>
                    <li><strong>Subject:</strong> "Invoice #[number]"</li>
                    <li><strong>Includes:</strong> Payment instructions and due date</li>
                </ul>

                <h4>When Invoice is Marked Paid:</h4>
                <ul>
                    <li><strong>Customer receives:</strong> "Payment received" confirmation email</li>
                    <li><strong>Invoice status:</strong> Changes to "Paid"</li>
                    <li><strong>You receive:</strong> Admin notification</li>
                </ul>

                <h3>Email Delivery Tracking</h3>
                <p>For every email sent by the system, you can see:</p>
                <ul>
                    <li><strong>Sent:</strong> Email was successfully delivered to the mail server</li>
                    <li><strong>Opened:</strong> Customer opened the email (we use a tracking pixel)</li>
                    <li><strong>Failed:</strong> Email could not be delivered (shows error reason)</li>
                </ul>

                <h3>View Email Delivery Log</h3>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to Reports → Email Delivery</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>You see every email sent from the system</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>Status shows: Sent, Opened, or Failed</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Click on a row to see full details and timestamps</p>
                        </div>
                    </div>
                </div>

                <h3>How Tracking Works</h3>
                <p>When we send an email to a customer, we include an invisible 1-pixel image. When they open the email, their email client downloads the image, and we know the email was opened. This is a standard marketing and business practice.</p>

                <h3>What Tracking Tells You</h3>
                <ul>
                    <li><strong>Email Sent:</strong> Successfully delivered (customer got it)</li>
                    <li><strong>Email Opened:</strong> Customer has read it</li>
                    <li><strong>Not Opened:</strong> Customer hasn't opened it yet (but it was delivered)</li>
                    <li><strong>Failed:</strong> Email bounced or was rejected (delivery failed)</li>
                </ul>

                <h3>WhatsApp Messaging</h3>
                <p>When you choose to send a quotation or invoice via WhatsApp:</p>
                <ol>
                    <li>System generates a WhatsApp message link (wa.me)</li>
                    <li>Link includes the quote/invoice details in the message</li>
                    <li>You can share this link or open WhatsApp to send</li>
                    <li>Customer receives a WhatsApp message with approval/payment link</li>
                </ol>

                <h3>Troubleshooting: Emails Not Sending</h3>

                <h4>Check SMTP Settings:</h4>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-step-number">1</div>
                        <div class="help-step-content">
                            <p>Go to Settings → Email Configuration</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">2</div>
                        <div class="help-step-content">
                            <p>Verify SMTP host, port, username, password are correct</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">3</div>
                        <div class="help-step-content">
                            <p>If using Gmail: Use app password, NOT your Gmail login password</p>
                        </div>
                    </div>
                    <div class="help-step">
                        <div class="help-step-number">4</div>
                        <div class="help-step-content">
                            <p>Click "Send Test Email" to verify configuration</p>
                        </div>
                    </div>
                </div>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> Check the Email Delivery report to see specific error messages for failed emails.</p>
                </div>
            </section>

            <!-- Section 12: Troubleshooting -->
            <section class="help-section" id="troubleshooting" data-help-section="troubleshooting">
                <h2>Troubleshooting</h2>

                <p>Find solutions to common issues here.</p>

                <h3>Emails Not Sending</h3>

                <h4>Problem: Quotes or invoices aren't being delivered to customers</h4>

                <h4>Solution:</h4>
                <ol>
                    <li><strong>Check SMTP Settings:</strong>
                        <ul>
                            <li>Go to Settings → Email Configuration</li>
                            <li>Verify SMTP host, port, username, password are filled in correctly</li>
                            <li>For Gmail: Make sure you're using the 16-character app password, not your Gmail login password</li>
                        </ul>
                    </li>
                    <li><strong>Send a Test Email:</strong>
                        <ul>
                            <li>Click "Send Test Email" button in Email Settings</li>
                            <li>Check if you receive it in your inbox</li>
                        </ul>
                    </li>
                    <li><strong>Check Email Delivery Log:</strong>
                        <ul>
                            <li>Go to Reports → Email Delivery</li>
                            <li>Find failed emails and note the error message</li>
                        </ul>
                    </li>
                    <li><strong>Common errors and fixes:</strong>
                        <ul>
                            <li><strong>"Authentication failed":</strong> Wrong password. Re-check your SMTP credentials.</li>
                            <li><strong>"Connection refused":</strong> Wrong host or port. Check email provider settings.</li>
                            <li><strong>"Recipient rejected":</strong> Customer email is invalid or doesn't exist.</li>
                        </ul>
                    </li>
                </ol>

                <h3>PDF Not Downloading</h3>

                <h4>Problem: When trying to download quotation or invoice PDF, nothing happens</h4>

                <h4>Solution:</h4>
                <ol>
                    <li><strong>Check Storage Health:</strong>
                        <p>Open Admin → Storage Health and confirm Cloudinary and Backblaze B2 are connected.</p>
                    </li>
                    <li><strong>Check Writable Temp Folders:</strong>
                        <p>Make sure the server can write to the framework cache and temporary PDF directory.</p>
                    </li>
                    <li><strong>Verify Company Logo and Signature Exist:</strong>
                        <ul>
                            <li>Go to Settings → Company Information</li>
                            <li>Make sure company logo is uploaded</li>
                            <li>Go to My Account → Upload Signature</li>
                            <li>Make sure signature is uploaded</li>
                        </ul>
                    </li>
                    <li><strong>Try Another Browser:</strong>
                        <ul>
                            <li>Sometimes it's a browser cache issue</li>
                            <li>Try Chrome, Firefox, or Safari</li>
                        </ul>
                    </li>
                    <li><strong>Clear Browser Cache:</strong>
                        <ul>
                            <li>Clear cookies and cached files</li>
                            <li>Try downloading again</li>
                        </ul>
                    </li>
                </ol>

                <h3>Signature Not Showing on PDF</h3>

                <h4>Problem: PDFs show "Signature not available" instead of your signature</h4>

                <h4>Solution:</h4>
                <ol>
                    <li><strong>Upload Your Signature:</strong>
                        <ul>
                            <li>Go to My Account → Profile → Your Signature</li>
                            <li>Upload a PNG or JPG image of your signature</li>
                            <li>Make sure the image is clear and legible</li>
                            <li>File size should be under 2MB</li>
                            <li>Click "Save"</li>
                        </ul>
                    </li>
                    <li><strong>Check Image Format:</strong>
                        <ul>
                            <li>Signature must be PNG or JPG format</li>
                            <li>Try converting the image if it's another format</li>
                        </ul>
                    </li>
                    <li><strong>Download PDF Again:</strong>
                        <ul>
                            <li>After uploading signature, try downloading the PDF again</li>
                            <li>The signature should now appear</li>
                        </ul>
                    </li>
                </ol>

                <h3>Cannot Edit Quote or Invoice</h3>

                <h4>Problem: Edit button is grayed out or not available</h4>

                <h4>Explanation:</h4>
                <p>This is by design. The system protects approved quotes and paid invoices from editing to maintain record integrity. Once a document is approved or paid, you cannot change its details.</p>

                <h4>Solution:</h4>
                <ul>
                    <li><strong>If you need to make changes:</strong>
                        <ul>
                            <li>Create a new quotation or invoice</li>
                            <li>Or duplicate the existing one and edit the copy</li>
                        </ul>
                    </li>
                    <li><strong>If data is wrong:</strong>
                        <ul>
                            <li>Contact your administrator</li>
                            <li>They may be able to help or grant special access</li>
                        </ul>
                    </li>
                </ul>

                <h3>Approval Link Not Working</h3>

                <h4>Problem: Customer clicks approval link and gets error</h4>

                <h4>Solution:</h4>
                <ol>
                    <li><strong>Check if Link Expired:</strong>
                        <ul>
                            <li>Approval links expire after 7 days</li>
                            <li>If more than 7 days have passed, the link is no longer valid</li>
                        </ul>
                    </li>
                    <li><strong>Resend the Quotation:</strong>
                        <ul>
                            <li>Go to the quotation</li>
                            <li>Click "Send" button</li>
                            <li>A new approval link will be generated</li>
                            <li>Send it again to the customer</li>
                        </ul>
                    </li>
                    <li><strong>Manually Approve:</strong>
                        <ul>
                            <li>If customer says they approved verbally, you can manually approve</li>
                            <li>Go to the quotation and click "Approve"</li>
                        </ul>
                    </li>
                </ol>

                <h3>Page Not Loading or Showing Old Data</h3>

                <h4>Problem: Page won't load or you're seeing outdated information</h4>

                <h4>Solution:</h4>
                <p>Run these commands (requires server access):</p>
                <pre>php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear</pre>

                <p>Or if you don't have server access:</p>
                <ul>
                    <li>Clear your browser cache and cookies</li>
                    <li>Refresh the page (Ctrl+F5 or Cmd+Shift+R)</li>
                    <li>Try a different browser</li>
                    <li>Log out and log back in</li>
                </ul>

                <h3>Two-Factor Code Not Received</h3>

                <h4>Problem: No 6-digit code in email during login</h4>

                <h4>Solution:</h4>
                <ol>
                    <li><strong>Check Spam Folder:</strong>
                        <ul>
                            <li>Check your email spam/junk folder</li>
                            <li>The code email might be filtered there</li>
                        </ul>
                    </li>
                    <li><strong>Verify Your Email Address:</strong>
                        <ul>
                            <li>Go to My Account → Profile</li>
                            <li>Make sure your email address is correct</li>
                            <li>Update if needed</li>
                        </ul>
                    </li>
                    <li><strong>Check SMTP Settings:</strong>
                        <ul>
                            <li>Go to Settings → Email Configuration</li>
                            <li>Verify email settings are configured correctly</li>
                        </ul>
                    </li>
                    <li><strong>Request New Code:</strong>
                        <ul>
                            <li>On the OTP verification page, click "Resend Code"</li>
                            <li>Wait for new code (check spam folder)</li>
                        </ul>
                    </li>
                    <li><strong>Code Expiration:</strong>
                        <ul>
                            <li>Codes expire in 10 minutes</li>
                            <li>If you missed the deadline, request a new code</li>
                        </ul>
                    </li>
                </ol>

                <h3>Customer Cannot Approve Quotation</h3>

                <h4>Problem: Customer clicks approval link but can't complete the process</h4>

                <h4>Solution:</h4>
                <ol>
                    <li><strong>Check Link Expiration:</strong>
                        <ul>
                            <li>Links expire after 7 days</li>
                            <li>Ask customer when they received the link</li>
                        </ul>
                    </li>
                    <li><strong>Try a Different Browser:</strong>
                        <ul>
                            <li>Compatibility issues might prevent form submission</li>
                            <li>Have customer try Chrome, Firefox, or Safari</li>
                        </ul>
                    </li>
                    <li><strong>Manually Approve:</strong>
                        <ul>
                            <li>If customer confirms verbally, you can approve manually</li>
                            <li>Go to quotation → Click "Approve"</li>
                        </ul>
                    </li>
                    <li><strong>Resend Link:</strong>
                        <ul>
                            <li>Go to quotation → Click "Send"</li>
                            <li>Generate fresh approval link</li>
                            <li>Have customer use the new link</li>
                        </ul>
                    </li>
                </ol>

                <div class="help-tip">
                    <p><strong><i data-lucide="lightbulb" class="icon-xs me-1"></i>Tip:</strong> When all else fails, check the Email Delivery report in Reports to see detailed error messages and status info.</p>
                </div>
            </section>

            <div id="help-no-results" style="display: none;">
                <div class="help-no-results">
                    <p>No results found. Try a different search term.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('help-search');
    const helpSections = document.querySelectorAll('.help-section');
    const navLinks = document.querySelectorAll('.help-nav-link');
    const noResults = document.getElementById('help-no-results');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();

        if (!query) {
            helpSections.forEach(s => s.style.display = 'block');
            noResults.style.display = 'none';
            return;
        }

        let visibleCount = 0;
        helpSections.forEach(section => {
            const text = section.textContent.toLowerCase();
            if (text.includes(query)) {
                section.style.display = 'block';
                visibleCount++;
            } else {
                section.style.display = 'none';
            }
        });

        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    });

    // Active nav link on scroll
    window.addEventListener('scroll', function() {
        let current = '';
        helpSections.forEach(section => {
            if (window.scrollY >= section.offsetTop - 100) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
});
</script>
@endsection
