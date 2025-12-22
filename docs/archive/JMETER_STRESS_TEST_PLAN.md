# JMeter Stress Testing Plan - POS Supermarket Application

## 📋 Overview
This document outlines a comprehensive stress testing strategy for the POS Supermarket application using Apache JMeter. The plan covers all critical workflows, user roles, and performance scenarios.

---

## 🎯 Testing Objectives

### Performance Goals
- **Response Time**: < 500ms for 95% of requests
- **Throughput**: Handle 200+ transactions per second
- **Error Rate**: < 0.1% under normal load
- **Concurrent Users**: Support 500+ simultaneous users
- **Availability**: 99.9% uptime under load

### Critical User Journeys
1. **Cashier Flow**: Login → Open Drawer → Process Sales → Logout
2. **Manager Flow**: Login → View Reports → Manage Inventory → Approve Requests
3. **Business Admin Flow**: Login → View Analytics → Manage Staff → Review Logs
4. **SuperAdmin Flow**: Login → Manage Businesses → System Overview

---

## 📊 Test Scenarios

### 1. Authentication Load Tests

#### Test 1.1: Role-Based Login
- **Users**: 50 concurrent (10 each role)
- **Duration**: 5 minutes
- **Endpoints**:
  - POST `/login/superadmin`
  - POST `/login/business-admin`
  - POST `/login/manager`
  - POST `/login/cashier`
- **Validation**: Verify session creation, role redirection, activity logging

#### Test 1.2: Failed Login Attempts
- **Users**: 20 concurrent
- **Duration**: 3 minutes
- **Scenario**: Invalid credentials, brute force simulation
- **Expected**: Rate limiting, proper error messages, no system degradation

#### Test 1.3: Session Management
- **Users**: 100 concurrent
- **Duration**: 30 minutes
- **Scenario**: Long-running sessions, session timeout (120 min), concurrent logins
- **Validation**: Session persistence, cookie handling, CSRF token management

---

### 2. POS Terminal & Sales Operations

#### Test 2.1: Cash Drawer Operations
- **Users**: 50 cashiers
- **Requests**: 
  - POST `/cash-drawer/open` (opening amount: 0-1000 GHS)
- **Validation**: One session per cashier per day, duplicate prevention

#### Test 2.2: High-Volume Sales Processing
- **Users**: 50 cashiers
- **Duration**: 10 minutes
- **Requests**: POST `/sales`
- **Payload**: 1-10 items per sale, various payment methods
- **Target**: 500+ sales/minute
- **Validation**:
  - Inventory deduction accuracy
  - COGS calculation correctness
  - Tax computation validation
  - Stock log creation
  - Activity log entries

#### Test 2.3: Concurrent Sales (Same Products)
- **Users**: 100 cashiers
- **Scenario**: Multiple cashiers selling same products simultaneously
- **Critical Test**: Database deadlock prevention, stock accuracy under contention
- **Validation**: No negative inventory, no lost updates

#### Test 2.4: POS Terminal Load (GET)
- **Users**: 50 concurrent
- **Endpoint**: GET `/terminal`
- **Validation**: Product catalog loading, category filtering, pagination performance

---

### 3. Product & Inventory Management

#### Test 3.1: Product Creation
- **Users**: 20 admins/managers
- **Duration**: 5 minutes
- **Requests**: POST `/product`
- **Payload**: Products with images, categories, pricing, box quantities
- **Validation**: File upload handling, validation errors, database integrity

#### Test 3.2: Bulk Import Operations
- **Users**: 10 admins
- **Requests**: POST `/inventory/bulk-import`
- **File Size**: 100-1000 rows
- **Validation**: Excel parsing, validation errors, transaction rollback on failure

#### Test 3.3: Stock Receipt Processing
- **Users**: 20 managers
- **Requests**: POST `/stock-receipts`
- **Scenario**: Receiving stock, updating inventory levels, FIFO queue management
- **Validation**: Correct cost basis, stock log accuracy, supplier invoice tracking

#### Test 3.4: Product Search & Filtering
- **Users**: 50 concurrent
- **Endpoint**: GET `/product` (with filters, search, pagination)
- **Validation**: Query performance, index utilization, result accuracy

---

### 4. Reporting & Analytics

#### Test 4.1: Sales Reports
- **Users**: 30 managers/admins
- **Duration**: 5 minutes
- **Endpoints**:
  - GET `/sales/report` (various date ranges, filters)
  - GET `/sales/export/csv`
  - GET `/sales/export/pdf`
- **Validation**: Complex query performance, export generation time, file size handling

#### Test 4.2: Product Analytics
- **Users**: 20 admins
- **Endpoints**:
  - GET `/product-reports/performance`
  - GET `/product-reports/profitability`
  - GET `/product-reports/trends`
- **Validation**: Join performance (sales → branches → business), aggregation accuracy

#### Test 4.3: Activity Logs
- **Users**: 10 business admins
- **Endpoint**: GET `/activity-logs`
- **Filters**: User, action, date range, search
- **Validation**: Pagination performance, search speed, filtering accuracy

---

### 5. Customer & Supplier Management

#### Test 5.1: Customer CRUD
- **Users**: 20 concurrent
- **Operations**: Create, Read, Update, Delete customers
- **Endpoints**: `/customers/*`
- **Validation**: Credit limit calculations, payment terms, customer number generation

#### Test 5.2: Supplier Operations
- **Users**: 15 managers
- **Operations**: Create/update suppliers, toggle status
- **Validation**: Local vs business-level supplier filtering

---

### 6. Staff & Branch Management

#### Test 6.1: Staff Assignment
- **Users**: 10 business admins
- **Requests**: POST `/admin/cashiers/assign`
- **Scenario**: Assigning cashiers to branches, role updates
- **Validation**: Branch capacity, role validation, notification sending

#### Test 6.2: Manager Item Requests
- **Users**: 15 managers
- **Requests**: POST `/manager/item-requests`
- **Scenario**: Requesting stock transfers, bulk uploads
- **Validation**: Request approval workflow, notification to business admin

---

## 🔥 Stress Test Scenarios

### Scenario A: Normal Business Hours Load
- **Total Users**: 100
  - 50 Cashiers (active POS)
  - 30 Managers (reporting/inventory)
  - 15 Business Admins (analytics)
  - 5 SuperAdmins (system monitoring)
- **Duration**: 1 hour
- **Ramp-up**: 10 minutes
- **Think Time**: 3-10 seconds between requests
- **Expected Behavior**: < 500ms response time, 0% errors

### Scenario B: Peak Hours Load
- **Total Users**: 200
  - 100 Cashiers
  - 60 Managers
  - 30 Business Admins
  - 10 SuperAdmins
- **Duration**: 30 minutes
- **Ramp-up**: 5 minutes
- **Think Time**: 2-5 seconds
- **Expected Behavior**: < 1s response time, < 0.5% errors

### Scenario C: Black Friday / Extreme Load
- **Total Users**: 500
  - 300 Cashiers (high-volume sales)
  - 150 Managers
  - 40 Business Admins
  - 10 SuperAdmins
- **Duration**: 15 minutes
- **Ramp-up**: 2 minutes
- **Think Time**: 1-3 seconds
- **Expected Behavior**: Identify breaking point, measure degradation

### Scenario D: Spike Test
- **Start**: 50 users → **Spike to**: 300 users → **Back to**: 50 users
- **Duration**: 20 minutes (5 min normal, 10 min spike, 5 min recovery)
- **Purpose**: Test auto-scaling, cache behavior, connection pool management

### Scenario E: Endurance Test
- **Users**: 100 constant
- **Duration**: 4 hours
- **Purpose**: Memory leaks, session buildup, database connection exhaustion

---

## 🛠️ JMeter Test Plan Structure

### Thread Groups

```
POS_Supermarket_Test_Plan.jmx
│
├── Config Elements
│   ├── HTTP Request Defaults (localhost:8000, /api/)
│   ├── HTTP Cookie Manager
│   ├── HTTP Cache Manager
│   ├── User Defined Variables
│   └── CSV Data Set Configs
│       ├── users.csv (email, password, role)
│       ├── products.csv (product_id, name, price, stock)
│       └── customers.csv (name, email, phone, credit_limit)
│
├── Thread Group: Cashier_Sales_Flow
│   ├── Cashier Login
│   ├── Open Cash Drawer
│   ├── Load POS Terminal
│   ├── Process Sale (Loop: 10 iterations)
│   └── Logout
│
├── Thread Group: Manager_Operations
│   ├── Manager Login
│   ├── View Sales Report
│   ├── Create Stock Receipt
│   ├── Approve/Reject Item Request
│   └── Logout
│
├── Thread Group: BusinessAdmin_Analytics
│   ├── Business Admin Login
│   ├── View Product Performance
│   ├── View Activity Logs
│   ├── Export Sales Report (CSV)
│   └── Logout
│
├── Thread Group: SuperAdmin_System
│   ├── SuperAdmin Login
│   ├── View All Businesses
│   ├── Manage System Users
│   └── Logout
│
├── Thread Group: Stress_Test_Mixed_Load
│   ├── Random Controller (weights based on role distribution)
│   └── All operations mixed
│
└── Listeners
    ├── View Results Tree (for debugging)
    ├── Aggregate Report
    ├── Response Time Graph
    ├── Transactions per Second
    ├── Active Threads Over Time
    └── Backend Listener (InfluxDB/Grafana optional)
```

---

## 📝 CSV Test Data Files

### users.csv
```csv
email,password,role,branch_id
superadmin@pos.com,password,superadmin,
businessadmin@pos.com,password,business_admin,
manager@pos.com,password,manager,1
cashier@pos.com,password,cashier,1
cashier1@pos.com,password,cashier,1
cashier2@pos.com,password,cashier,1
```

### products.csv
```csv
product_id,name,price,cost_price,stock_quantity
1,Coca Cola 500ml,3.50,2.00,100
2,Rice 5kg,45.00,30.00,50
3,Milk 1L,12.00,8.00,75
```

### sale_items.csv
```csv
product_id,quantity,payment_method
1,2,cash
2,1,mobile_money
3,3,card
```

---

## 🔧 JMeter Configuration

### HTTP Request Defaults
- **Protocol**: http
- **Server**: localhost (or test server IP)
- **Port**: 8000
- **Content Encoding**: UTF-8

### HTTP Cookie Manager
- **Clear cookies each iteration**: false
- **Policy**: Standard

### HTTP Header Manager (Global)
```
Accept: text/html,application/json
Content-Type: application/json
User-Agent: JMeter-StressTest/1.0
X-Requested-With: XMLHttpRequest
```

### CSRF Token Extraction
```groovy
// Regular Expression Extractor
// Apply to: Main sample only
// Field to check: Body
// Regular Expression: <input type="hidden" name="_token" value="(.+?)">
// Template: $1$
// Match No.: 1
// Default: CSRF_TOKEN_NOT_FOUND
```

---

## 📈 Performance Metrics to Monitor

### Application Metrics
- **Response Time**: Average, Median, 90th percentile, 95th percentile, 99th percentile
- **Throughput**: Requests per second
- **Error Rate**: Percentage of failed requests
- **Active Threads**: Concurrent users over time

### System Metrics (Server-side)
- **CPU Usage**: Target < 80% under peak load
- **Memory Usage**: Monitor for leaks
- **Database Connections**: Pool utilization, wait times
- **Disk I/O**: Read/write operations (uploads, logs)
- **Network Bandwidth**: Request/response sizes

### Database Metrics (MySQL)
- **Query Performance**: Slow query log analysis
- **Connection Pool**: Active, idle, waiting connections
- **Lock Contention**: Deadlocks, table locks during concurrent sales
- **Transaction Throughput**: Commits per second

### Laravel Metrics
- **Queue Jobs**: Processing rate (if using queues)
- **Cache Hit Rate**: Redis/Memcached performance
- **Session Storage**: Database session table size
- **Log File Size**: storage/logs/laravel.log growth

---

## 🚀 Test Execution Plan

### Phase 1: Baseline Tests (Week 1)
1. **Day 1-2**: Setup JMeter, create test plan, prepare test data
2. **Day 3**: Run authentication tests (1-10 users)
3. **Day 4**: Run CRUD operation tests (5-20 users)
4. **Day 5**: Establish performance baselines, document results

### Phase 2: Load Tests (Week 2)
1. **Day 1**: Normal load test (50-100 users)
2. **Day 2**: Peak load test (100-200 users)
3. **Day 3**: Analyze results, optimize application
4. **Day 4**: Re-test after optimizations
5. **Day 5**: Document findings, performance improvements

### Phase 3: Stress Tests (Week 3)
1. **Day 1**: Stress test (300-500 users)
2. **Day 2**: Spike test
3. **Day 3**: Endurance test (4 hours)
4. **Day 4**: Analyze breaking points, memory leaks
5. **Day 5**: Final report, recommendations

---

## 🔍 Critical Test Cases

### High Priority
1. ✅ **Concurrent Sales**: 100 cashiers selling same products
2. ✅ **Cash Drawer Session**: Prevent duplicate sessions
3. ✅ **Inventory Accuracy**: Stock levels under concurrent updates
4. ✅ **CSRF Protection**: Token validation under load
5. ✅ **Session Management**: 120-minute timeout handling
6. ✅ **Activity Logging**: All actions logged without blocking requests
7. ✅ **Database Transactions**: No lost updates, proper rollback

### Medium Priority
8. File Uploads: Large product images, bulk Excel imports
9. Report Generation: PDF/CSV exports under load
10. Notification System: High-value sale notifications
11. Search Performance: Product/customer search with filters
12. Pagination: Large result sets (10,000+ records)

### Low Priority
13. Map Visualization: Business/branch location rendering
14. Real-time Updates: Dashboard statistics refresh
15. Email Sending: Password resets, user creation

---

## 📊 Success Criteria

### Functional Requirements
- ✅ All API endpoints return correct responses
- ✅ No data corruption under concurrent access
- ✅ All business rules enforced (role permissions, validations)
- ✅ Activity logs capture all critical actions

### Performance Requirements
- ✅ 95% of requests complete within 500ms (normal load)
- ✅ 99% of requests complete within 2s (peak load)
- ✅ Error rate < 0.1% under normal load
- ✅ Error rate < 1% under peak load
- ✅ System handles 200+ transactions/second
- ✅ No memory leaks over 4-hour endurance test

### Scalability Requirements
- ✅ Linear performance degradation up to 200 users
- ✅ Graceful degradation beyond 200 users
- ✅ System recovers quickly after spike load

---

## 🛡️ Risk Mitigation

### Identified Risks
1. **Database Deadlocks**: Concurrent sales on same products
   - *Mitigation*: Use row-level locking, optimize transaction scope
   
2. **Session Storage Exhaustion**: 500+ concurrent sessions
   - *Mitigation*: Use Redis for sessions, implement cleanup jobs

3. **File Storage Limits**: Bulk product image uploads
   - *Mitigation*: Implement file size limits, use cloud storage (S3)

4. **Memory Leaks**: Long-running processes
   - *Mitigation*: Monitor memory, implement process recycling

5. **CSRF Token Stale**: High-frequency requests
   - *Mitigation*: Increase token lifetime, implement token refresh

---

## 📋 Test Execution Checklist

### Pre-Test
- [ ] Backup production database
- [ ] Set up isolated test environment
- [ ] Prepare test data (users, products, branches)
- [ ] Configure JMeter distributed testing (if needed)
- [ ] Set up monitoring tools (Grafana, New Relic, etc.)
- [ ] Notify team about test schedule
- [ ] Disable rate limiting (for testing)
- [ ] Clear application cache and logs

### During Test
- [ ] Monitor server resources (CPU, memory, disk)
- [ ] Watch database connections and slow queries
- [ ] Check application logs for errors
- [ ] Verify cache hit rates
- [ ] Monitor network latency
- [ ] Track error rates in real-time

### Post-Test
- [ ] Generate JMeter HTML dashboard report
- [ ] Analyze slow queries from database logs
- [ ] Review Laravel logs for exceptions
- [ ] Calculate performance metrics
- [ ] Compare against baseline
- [ ] Document bottlenecks and issues
- [ ] Create optimization recommendations
- [ ] Share results with development team

---

## 📈 Expected Bottlenecks

1. **Database Query Performance**
   - Product analytics joins (sales → branches → business)
   - Activity logs pagination with filters
   - Real-time inventory stock queries

2. **File I/O Operations**
   - Product image uploads
   - CSV/PDF export generation
   - Laravel log file writes

3. **Session Management**
   - Database session driver under 500+ users
   - Cookie size with extensive user data

4. **External Dependencies**
   - Email sending (SMTP)
   - SMS notifications (for user creation)
   - Cloud storage (if using S3)

---

## 🔧 Optimization Recommendations

### Database
- Add indexes on frequently queried columns (branch_id, business_id, created_at)
- Optimize complex joins in product reports
- Implement query result caching for reports
- Use read replicas for analytics queries

### Application
- Enable OPcache for PHP
- Use Redis for session storage instead of database
- Implement queue workers for background jobs (notifications, exports)
- Add CDN for static assets
- Enable HTTP/2 and compression

### Infrastructure
- Implement horizontal scaling (multiple app servers)
- Set up load balancer (Nginx, HAProxy)
- Use connection pooling for database
- Implement rate limiting per role

---

## 📝 Next Steps

1. **Review this plan** with the development team
2. **Set up JMeter environment** (Download JMeter 5.6+, Java 11+)
3. **Create test data** (seed database with realistic data)
4. **Build .jmx test plan** following the structure above
5. **Run baseline tests** to establish current performance
6. **Execute load tests** progressively increasing users
7. **Analyze results** and identify bottlenecks
8. **Optimize application** based on findings
9. **Re-test** to validate improvements
10. **Document final results** and recommendations

---

## 📞 Support & Resources

- **JMeter Documentation**: https://jmeter.apache.org/usermanual/
- **Laravel Performance**: https://laravel.com/docs/10.x/optimization
- **Database Tuning**: MySQL Performance Tuning Guide
- **Monitoring Tools**: Grafana, New Relic, Laravel Telescope

---

**Test Plan Version**: 1.0  
**Created**: December 3, 2025  
**Application**: POS Supermarket  
**Technology Stack**: Laravel 10, MySQL, Tailwind CSS, Alpine.js
