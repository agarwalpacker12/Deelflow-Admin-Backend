## Subscription

### List subscription packs

*   **Description:** List subscription packs
*   **Method:** GET
*   **Endpoint:** `/api/subscription-packs`
*   **Headers:** `Authorization: Bearer {token}`
*   **Response (Success - 200 Success):**

    ```json
   {
    "status": "success",
    "message": "Subscription packages retrieved successfully",
    "data": [
            {
                "id": 6,
                "name": "Basic",
                "description": "THis is basic product",
                "amount": 50,
                "currency": "usd",
                "interval": "month",
                "stripe_product_id": "prod_SqXN9JufsOBtXq",
                "stripe_price_id": "price_1RuqIiE0wE8Cg1kn9WxFPHxI",
                "created_at": "2025-08-11T10:15:52.000000Z",
                "updated_at": "2025-08-11T10:15:52.000000Z"
            },
            {
                "id": 5,
                "name": "Professional",
                "description": "",
                "amount": 100,
                "currency": "usd",
                "interval": "month",
                "stripe_product_id": "prod_SqXNgoWnOH5RaC",
                "stripe_price_id": "price_1RuqJ5E0wE8Cg1kn942czE1F",
                "created_at": "2025-08-11T10:15:52.000000Z",
                "updated_at": "2025-08-11T10:15:52.000000Z"
            },
            {
                "id": 4,
                "name": "Enterprise",
                "description": "Enterprise",
                "amount": 200,
                "currency": "usd",
                "interval": "month",
                "stripe_product_id": "prod_SqXOD2sgmc458Q",
                "stripe_price_id": "price_1RuqJRE0wE8Cg1knGpSwxESM",
                "created_at": "2025-08-11T10:15:52.000000Z",
                "updated_at": "2025-08-11T10:15:52.000000Z"
            }
        ]
    }
    ```

### Initiate Stripe Payment Session

*   **Description:** Initiate Payment Session
*   **Method:** POST
*   **Headers:** `Authorization: Bearer {token}`
*   **Endpoint:** `/api/create-checkout-session`
*   **Request:**

        ```json
        {
            "package_id": 6
        }
        ```

    *   **Response (Success - 200 Success):**

        ```json
        {
            "status": "success",
            "message": "Checkout session created successfully",
            "data": {
                "redirect_url": "https://checkout.stripe.com/c/pay/cs_test_a1zRnk9A18JNoruqFpci9BLtFne4NjOW3R1cjLoazC0uVlZ7LGtgt7pMJ6#fidkdWxOYHwnPyd1blpxYHZxWjA0S3xJb0pANXJAPUZiNG5rc1dVcUh1YF9dRDNcZF1OZFNmcH0yUTZVT3EzUE1talB9akd0N1ZBfFJkSTBqQzBccH8xfGBqVzx9MklucEYwNG81TmZxNm9VNTU9ZkhObUBRaicpJ2N3amhWYHdzYHcnP3F3cGApJ2lkfGpwcVF8dWAnPyd2bGtiaWBabHFgaCcpJ2BrZGdpYFVpZGZgbWppYWB3dic%2FcXdwYHgl"
            }
        }
        ```

### Initiate Customer Stripe Session

*   **Description:** Initiate Customer Stripe Session to manage subscription download invoice etc.
*   **Method:** POST
*   **Headers:** `Authorization: Bearer {token}`
*   **Endpoint:** `/api/create-customer-portal-session`
*   **Request:**

    ```json
    {
    }
    ```
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "User portal session created successfully",
        "data": {
            "redirect_url": "https://billing.stripe.com/p/session/test_YWNjdF8xTnlMak9FMHdFOENnMWtuLF9TcWhkbEJxUVRLeWRTNnk3TkFIbVZMZDdqZTN1Z1lu0100AARMo5tq"
        }
    }
    ```

### Retrieves Last 10 Invoice List

*   **Description:** Retrieves Last 10 Invoice List.
*   **Method:** GET
*   **Endpoint:** `/api/stripe-invoice`
*   **Headers:** `Authorization: Bearer {token}`
*   **Response (Success):**

    ```json
    {
        "status": "success",
        "message": "Invoices retrieved successfully",
        "data": [
            {
                "id": "in_1RuzyCE0wE8Cg1kn0LGSLTQU",
                "object": "invoice",
                "account_country": "US",
                "account_name": "Kration LLC",
                "account_tax_ids": null,
                "amount_due": 5000,
                "amount_overpaid": 0,
                "amount_paid": 5000,
                "amount_remaining": 0,
                "amount_shipping": 0,
                "application": null,
                "attempt_count": 0,
                "attempted": true,
                "auto_advance": false,
                "automatic_tax": {
                    "disabled_reason": null,
                    "enabled": false,
                    "liability": null,
                    "provider": null,
                    "status": null
                },
                "automatically_finalizes_at": null,
                "billing_reason": "subscription_create",
                "collection_method": "charge_automatically",
                "created": 1754935036,
                "currency": "usd",
                "custom_fields": null,
                "customer": "cus_SqeciAuyLwHEG2",
                "customer_address": null,
                "customer_email": "prosun@mailinator.com",
                "customer_name": "Prosun Halder",
                "customer_phone": null,
                "customer_shipping": null,
                "customer_tax_exempt": "none",
                "customer_tax_ids": [],
                "default_payment_method": null,
                "default_source": null,
                "default_tax_rates": [],
                "description": null,
                "discounts": [],
                "due_date": null,
                "effective_at": 1754935036,
                "ending_balance": 0,
                "footer": null,
                "from_invoice": null,
                "hosted_invoice_url": "https://invoice.stripe.com/i/acct_1NyLjOE0wE8Cg1kn/test_YWNjdF8xTnlMak9FMHdFOENnMWtuLF9TcWhNVmpQSVgxZWNsWlIzMWlZSmdlRFFDdHdhS0l3LDE0NTQ3Njk2NA0200LDKv65mr?s=ap",
                "invoice_pdf": "https://pay.stripe.com/invoice/acct_1NyLjOE0wE8Cg1kn/test_YWNjdF8xTnlMak9FMHdFOENnMWtuLF9TcWhNVmpQSVgxZWNsWlIzMWlZSmdlRFFDdHdhS0l3LDE0NTQ3Njk2NA0200LDKv65mr/pdf?s=ap",
                "issuer": {
                    "type": "self"
                },
                "last_finalization_error": null,
                "latest_revision": null,
                "lines": {
                    "object": "list",
                    "data": [
                        {
                            "id": "il_1RuzyCE0wE8Cg1kn3idxSNek",
                            "object": "line_item",
                            "amount": 5000,
                            "currency": "usd",
                            "description": "1 × Basic (at $50.00 / month)",
                            "discount_amounts": [],
                            "discountable": true,
                            "discounts": [],
                            "invoice": "in_1RuzyCE0wE8Cg1kn0LGSLTQU",
                            "livemode": false,
                            "metadata": [],
                            "parent": {
                                "invoice_item_details": null,
                                "subscription_item_details": {
                                    "invoice_item": null,
                                    "proration": false,
                                    "proration_details": {
                                        "credited_items": null
                                    },
                                    "subscription": "sub_1RuzyEE0wE8Cg1knXLIib9xH",
                                    "subscription_item": "si_SqhMlvkyTwDpNt"
                                },
                                "type": "subscription_item_details"
                            },
                            "period": {
                                "end": 1757613436,
                                "start": 1754935036
                            },
                            "pretax_credit_amounts": [],
                            "pricing": {
                                "price_details": {
                                    "price": "price_1RuqIiE0wE8Cg1kn9WxFPHxI",
                                    "product": "prod_SqXN9JufsOBtXq"
                                },
                                "type": "price_details",
                                "unit_amount_decimal": "5000"
                            },
                            "quantity": 1,
                            "taxes": []
                        }
                    ],
                    "has_more": false,
                    "total_count": 1,
                    "url": "/v1/invoices/in_1RuzyCE0wE8Cg1kn0LGSLTQU/lines"
                },
                "livemode": false,
                "metadata": [],
                "next_payment_attempt": null,
                "number": "IOF0R9KF-0016",
                "on_behalf_of": null,
                "parent": {
                    "quote_details": null,
                    "subscription_details": {
                        "metadata": [],
                        "subscription": "sub_1RuzyEE0wE8Cg1knXLIib9xH"
                    },
                    "type": "subscription_details"
                },
                "payment_settings": {
                    "default_mandate": null,
                    "payment_method_options": {
                        "acss_debit": null,
                        "bancontact": null,
                        "card": {
                            "request_three_d_secure": "automatic"
                        },
                        "customer_balance": null,
                        "konbini": null,
                        "sepa_debit": null,
                        "us_bank_account": null
                    },
                    "payment_method_types": [
                        "card"
                    ]
                },
                "period_end": 1754935036,
                "period_start": 1754935036,
                "post_payment_credit_notes_amount": 0,
                "pre_payment_credit_notes_amount": 0,
                "receipt_number": null,
                "rendering": null,
                "shipping_cost": null,
                "shipping_details": null,
                "starting_balance": 0,
                "statement_descriptor": null,
                "status": "paid",
                "status_transitions": {
                    "finalized_at": 1754935036,
                    "marked_uncollectible_at": null,
                    "paid_at": 1754935037,
                    "voided_at": null
                },
                "subtotal": 5000,
                "subtotal_excluding_tax": 5000,
                "test_clock": null,
                "total": 5000,
                "total_discount_amounts": [],
                "total_excluding_tax": 5000,
                "total_pretax_credit_amounts": [],
                "total_taxes": [],
                "webhooks_delivered_at": 1754935040
            },
            {},{},{},....
        ]
    }
    ```

### Get Current Subscription

*   **Description:** Get Current Subscription
*   **Method:** GET
*   **Endpoint:** `/api/current-subscription`
*   **Headers:** `Authorization: Bearer {token}`
*   **Response (Success):**

    ```json
    {
    "status": "success",
    "message": "Subscription retrieved successfully",
    "data": {
        "id": 1,
        "organization_id": 1,
        "user_id": 73,
        "package_id": 1,
        "stripe_subscription_id": "sub_1RvZtmE0wE8Cg1knguwnFnnQ",
        "stripe_customer_id": "cus_SrIULkYZt2oSSS",
        "stripe_price_id": "price_1RuqJRE0wE8Cg1knGpSwxESM",
        "status": "active",
        "current_period_end": "2025-09-13 08:19:04",
        "card_last4": "4242",
        "card_brand": "visa",
        "created_at": "2025-08-13T08:19:09.000000Z",
        "updated_at": "2025-08-13T08:19:09.000000Z",
        "package": {
            "id": 1,
            "name": "Enterprise",
            "description": "Enterprise",
            "amount": 200,
            "currency": "usd",
            "interval": "month",
            "stripe_product_id": "prod_SqXOD2sgmc458Q",
            "stripe_price_id": "price_1RuqJRE0wE8Cg1knGpSwxESM",
            "created_at": "2025-08-13T08:14:42.000000Z",
            "updated_at": "2025-08-13T08:14:42.000000Z"
        },
        "organization": {
            "id": 1,
            "uuid": "b69ae2c0-07bc-415d-b9a7-32ad921c59f0",
            "name": "protech",
            "created_at": "2025-08-08T07:00:22.000000Z",
            "updated_at": "2025-08-13T08:19:09.000000Z",
            "slug": "protech",
            "industry": null,
            "organization_size": null,
            "business_email": null,
            "business_phone": null,
            "website": null,
            "support_email": null,
            "street_address": null,
            "city": null,
            "state_province": null,
            "zip_postal_code": null,
            "country": null,
            "timezone": null,
            "language": null,
            "subscription_status": "active"
        }
    }
    }

