# OZJ Reporting System — Laravel + React

## Planning
- [x] Analyze data files (JSON workflow, Excel, Google Sheets)
- [/] Write implementation plan
- [ ] Get user approval

## Backend (Laravel)
- [ ] Create Laravel project
- [ ] Configure MySQL database connection
- [ ] Create database migrations (users, tickets, ticket_actions)
- [ ] Create models & relationships
- [ ] Build Auth system (login, JWT/Sanctum)
- [ ] Build API endpoints:
  - [ ] Tickets CRUD
  - [ ] Ticket progress/action updates
  - [ ] Dashboard statistics
  - [ ] Google Sheets sync
  - [ ] n8n webhook receiver
- [ ] Seed initial data from spreadsheet

## Frontend (React)
- [ ] Create React project (Vite)
- [ ] Setup routing & auth context
- [ ] Login page
- [ ] Dashboard page with charts
- [ ] Ticket list page with tracking
- [ ] Ticket detail page with progress update
- [ ] Manual ticket input form
- [ ] Responsive & premium design

## Integration & Verification
- [ ] Connect frontend to backend API
- [ ] Test n8n webhook endpoint
- [ ] Run both servers locally
- [ ] Verify all features work end-to-end
