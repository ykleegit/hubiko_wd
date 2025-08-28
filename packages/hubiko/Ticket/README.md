# Ticket Module for Hubiko SaaS

## Overview

The Ticket Module is a comprehensive ticketing system for the Hubiko SaaS platform. It allows customers and team members to create, track, and resolve support tickets efficiently. The module provides a centralized way to manage customer issues, assign them to team members, categorize and prioritize requests, and maintain complete conversation histories.

## Installation

### Automatic Installation (Recommended)

1. Log in to your Hubiko admin dashboard
2. Navigate to **Add-Ons > Browse Add-Ons**
3. Find the Ticket Module and click **Install**
4. Follow the on-screen instructions to complete installation
5. Activate the module from **Add-Ons > Manage Add-Ons**

### Manual Installation

1. Download the Ticket Module package
2. Extract the package to `packages/workdo/Ticket` in your Hubiko installation
3. Register the module in the database:
   ```php
   $addon = new \App\Models\AddOn;
   $addon->module = 'Ticket';
   $addon->name = 'Ticket';
   $addon->monthly_price = 0;
   $addon->yearly_price = 0;
   $addon->package_name = 'ticket';
   $addon->save();
   ```
4. Run migrations and seeders:
   ```bash
   php artisan migrate --path=/packages/workdo/Ticket/src/Database/Migrations
   php artisan package:seed Ticket
   ```
5. Clear cache and optimize:
   ```bash
   php artisan optimize:clear
   ```

## Features

- **Ticket Management**: Create, edit, and delete support tickets
- **Conversation Threading**: Maintain complete conversation history for each ticket
- **Assignment**: Assign tickets to specific team members
- **Categorization**: Organize tickets by categories and departments
- **Prioritization**: Set ticket priorities (Low, Medium, High, Critical)
- **Status Tracking**: Track ticket status (Open, In Progress, Closed, etc.)
- **File Attachments**: Attach files to tickets and conversations
- **Email Notifications**: Automated notifications for ticket updates
- **Dashboard**: Visual overview of ticket statistics
- **Search & Filtering**: Advanced search and filtering options
- **Multi-Tenancy**: Complete workspace isolation for multi-company environments

## Configuration

### General Settings

Navigate to **Settings > Ticket Settings** to configure:

- Default ticket categories
- Default priorities
- Auto-assignment rules
- Email notification templates
- Ticket number format
- SLA policies

### Permissions

The module provides the following permissions:

- `ticket manage`: Access to ticket dashboard and listings
- `ticket create`: Ability to create new tickets
- `ticket edit`: Ability to edit existing tickets
- `ticket delete`: Ability to delete tickets
- `ticket show`: Ability to view ticket details
- `ticket reply`: Ability to reply to tickets
- `ticket settings`: Access to ticket settings

Assign these permissions to roles as needed through the Roles & Permissions section.

## Usage Guide

### Creating a Ticket

1. Navigate to **Tickets > All Tickets**
2. Click the **Create** button
3. Fill in the required fields:
   - Subject
   - Description
   - Category
   - Priority
4. Assign to a team member (optional)
5. Add attachments if needed
6. Click **Save** to create the ticket

### Managing Tickets

1. View all tickets at **Tickets > All Tickets**
2. Use filters to sort by status, priority, category, or assignee
3. Click on a ticket to view details
4. Update status, priority, or assignment as needed
5. Add replies to maintain conversation history
6. Close the ticket when resolved

### Dashboard

The ticket dashboard provides at-a-glance metrics:

- Tickets by status
- Tickets by priority
- Recent activity
- Performance metrics
- SLA compliance

### Email Integration

The module can be configured to:

- Create tickets from incoming emails
- Send notifications on ticket updates
- Allow replies via email

## Support

For additional support or questions, please contact the Hubiko support team at support@hubiko.com or open a ticket in the system. 