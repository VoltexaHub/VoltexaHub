# VoltexaHub — Design Spec
**Date:** 2026-04-18  
**Status:** Approved  
**Owner:** joogiebear

---

## Overview

VoltexaHub is a closed-source, personal-use forum platform built for a tech audience (web dev, server administration, code, deployment). Inspired by MyBB and Xenforo but built with a modern stack. Single baked-in theme — no plugin or theme system.

---

## Tech Stack

| Layer | Choice |
|---|---|
| Backend | PHP 8.4 + Laravel 13 |
| Frontend | Vue 3 + Inertia.js |
| Styling | Tailwind CSS |
| Database | PostgreSQL |
| Cache / Queue | Redis |
| WebServer | Caddy (auto-HTTPS) |
| OS | Ubuntu 24.04 LTS |
| Auth guard | Cloudflare Turnstile (all auth forms) |

**No plugin system. No theme loader.** One polished theme, dark + light mode toggle.

---

## Visual Design

- **Style:** Deep navy backgrounds (`#1a1a2e` / `#16213e`) with purple→blue gradient accents (`#7c3aed` → `#2563eb`)
- **Layout:** Top navigation bar + full-width content (classic forum layout, no sidebar)
- **Thread view:** Classic two-column — left column has avatar + username + group + post count + credits badge; right column has post content
- **Post editor:** Markdown with live split-pane preview; syntax highlighting for code blocks
- **Responsive:** Mobile-first, tested from 375px up to 4K (2560px+)
- **Light mode:** Mirrors dark palette with inverted surface colors, same accent colors

---

## Directory Structure

Domain-based rather than type-based:

```
app/
  Auth/
  Forum/          # categories, forums, threads, posts
  User/           # profiles, groups, memberlist
  Moderation/     # reports, mod log, mod tools
  Credits/        # transactions, awards
  Shop/           # items, purchases
  Messaging/      # conversations, messages, friends
  Admin/          # admin panel controllers
  Settings/       # site settings, user settings
resources/
  js/             # Vue 3 pages + components (Inertia)
  css/            # Tailwind
database/
  migrations/
  seeders/
deploy/
  setup.sh        # interactive VPS setup script
```

---

## Database Schema

### Auth & Users
```
users               id, username, email, password, avatar, bio, signature,
                    group_id, is_trusted, credits, post_count, thread_count,
                    last_seen_at, email_verified_at, banned_at, banned_reason,
                    referral_code, referred_by_id, created_at

groups              id, name, color, icon, is_staff, permissions (JSON),
                    display_order

user_friends        user_id, friend_id, status (pending/accepted/blocked),
                    created_at
```

### Forums
```
categories          id, name, description, display_order

forums              id, category_id, name, description, icon, display_order,
                    thread_count, post_count, last_post_id

threads             id, forum_id, user_id, title, slug, is_pinned, is_locked,
                    is_deleted, views, reply_count, last_post_id, created_at

posts               id, thread_id, user_id, body (markdown), is_deleted,
                    edited_at, edited_by_id, created_at

post_reactions      user_id, post_id, type (like/etc)
```

### Moderation
```
reports             id, reporter_id, reportable_type, reportable_id, reason,
                    status (open/resolved/dismissed), resolved_by, created_at

mod_log             id, moderator_id, action, target_type, target_id,
                    note, created_at
```

### Credits & Awards
```
credit_transactions id, user_id, amount, type (earned/spent), reason,
                    reference_type, reference_id, created_at

awards              id, name, description, icon, criteria_type
                    (post_count/thread_count/account_age/referrals/manual),
                    criteria_value, created_at

user_awards         user_id, award_id, awarded_at
```

### Shop
```
shop_items          id, name, description, type (name_color/title/badge/
                    signature_upgrade/etc), price, data (JSON), is_active,
                    display_order

user_purchases      id, user_id, shop_item_id, purchased_at, is_active
```

### Messaging
```
conversations       id, subject, created_by_id, created_at

conversation_participants
                    conversation_id, user_id, last_read_at, left_at

messages            id, conversation_id, user_id, body (markdown), created_at
```

### Settings
```
settings            key, value  (site-wide: name, logo, tagline, etc.)

user_settings       user_id, key, value
                    Keys: pm_privacy (everyone/friends/trusted/nobody),
                          notify_pm, notify_reply, notify_mention
```

---

## Build Phases

### Phase 1 — Foundation

**Auth**
- Register, login, logout, forgot password, reset password, email verification
- Cloudflare Turnstile on all auth forms
- Remember me, session management

**Forums**
- Forum index: categories → forums with last post info + thread/post counts
- Thread list: pinned threads first, sortable, pagination
- Thread view: paginated posts, classic two-column layout
- Post reply with markdown editor + live preview
- Code syntax highlighting (highlight.js or Shiki)
- Thread search (basic, within forum)

**Admin Panel**
- Dashboard with site stats
- Categories: create, edit, delete, drag-reorder
- Forums: create, edit, delete, drag-reorder within category, move between categories
- Threads: list, pin, lock, move, delete, bulk actions
- Posts: view, edit, delete
- Users: list, view, edit group, ban/unban, reset password
- Groups: create, edit, delete, set permissions, set display order
- Settings: site name, logo, tagline, maintenance mode

**Mod Tools**
- Pin / lock / unlock / move / delete threads from thread view
- Edit / delete posts inline
- Report queue: view open reports, resolve or dismiss, mod log entry auto-created
- Bulk thread actions on forum page (already shipped in prior build)

**Theme**
- Dark / light toggle (persisted to user preference or localStorage for guests)
- Fully responsive: mobile nav collapses to hamburger menu
- User group color badges on posts and profiles

---

### Phase 2 — Community

**Profiles**
- Public profile: avatar, username, group badge, bio, signature, join date, last seen, post count, thread count, credit balance, awards shelf, recent posts tab

**Memberlist**
- Paginated list of all members, filterable by group, searchable by username

**Groups Page**
- All groups listed with member count, icon, color

**Staff Page**
- All groups flagged `is_staff = true`, listed with their members

**Credits**
Earned automatically via event listeners:
| Action | Credits |
|---|---|
| New post | +2 |
| New thread | +5 |
| Post reaction received | +1 |
| Report accepted by mod | +10 |
| Referral signup | +25 |
| Account age: 30 days | +50 |
| Account age: 1 year | +200 |

All transactions recorded to `credit_transactions` with reason.

**Awards**
- Defined by admin in admin panel (criteria type + value)
- Checked and awarded automatically on relevant events (post created, account age, etc.)
- Displayed on profile in an awards shelf

**Shop**
- Browse available items by category
- Purchase with credits (insufficient balance shows clear error)
- Item types to start: name color, custom title, profile badge, extended signature length
- Purchased items activate immediately; name colors and titles show on posts + profile
- Admin can add/edit/remove shop items

---

### Phase 3 — Social

**Private Messaging**
- Compose new conversation (to one or more participants), subject + markdown body
- Inbox: list conversations, unread count badge in nav
- Conversation view: threaded messages, reply box
- Leave conversation

**Friend System**
- Send friend request from profile
- Accept / decline / block
- Friends list on profile
- Block list in user settings

**Trusted User Status**
- `is_trusted` flag on `users` table, set by admin in admin panel
- Trusted users bypass "friends only" PM restrictions

**User Settings Page**
- PM privacy: everyone / friends only / trusted users only / nobody
- Email notifications: PM received, thread reply, mention
- Profile privacy: show online status, show last seen
- Connected: change email, change password
- Danger zone: delete account

---

## Deployment — `setup.sh`

Interactive bash script for fresh Ubuntu 24.04 LTS VPS. Prompts:

1. Domain name (e.g. `forum.example.com`)
2. Admin username
3. Admin email
4. Admin password
5. SMTP host, port, username, password, from address
6. DB password (auto-generated default offered)

**Script installs:**
- PHP 8.4 + required extensions
- Composer
- PostgreSQL 16
- Redis
- Node.js 22 LTS + npm
- Caddy

**Script configures:**
- Creates DB + user
- Clones/copies app files to `/var/www/voltexahub`
- Sets up `.env` from prompts
- Runs `composer install --no-dev`, `npm ci`, `npm run build`
- Runs `php artisan migrate --seed`
- Creates admin account via artisan command
- Caddy: writes Caddyfile with auto-HTTPS for provided domain
- systemd: queue worker service (`voltexahub-worker`)
- Sets correct file permissions

---

## Permissions Model

Groups have a `permissions` JSON column. Key permission flags:

```json
{
  "can_post": true,
  "can_create_thread": true,
  "can_upload_avatar": true,
  "can_use_signature": true,
  "can_react": true,
  "is_moderator": false,
  "is_admin": false
}
```

Admin group always has full access regardless of permission flags. Moderator flag grants access to mod tools but not admin panel.

---

## Cloudflare Turnstile

Integrated on: register, login, forgot password forms.  
Server-side verification via Turnstile's siteverify API before processing any auth action.  
Site key + secret key stored in `.env`.

---

## Key Constraints

- Closed source, personal use — no license file, no public release
- No plugin system, no theme system — single codebase, single theme
- PHP 8.4 features used where appropriate (property hooks, asymmetric visibility)
- All credits changes go through `credit_transactions` — no direct balance mutations
- Schema will evolve; this is the starting foundation
