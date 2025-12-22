# JMeter Stress Testing - Complete Checklist

## 📋 Phase 1: Setup & Preparation

### Environment Setup
- [ ] Install Apache JMeter 5.6+
  - [ ] Download from https://jmeter.apache.org/download_jmeter.cgi
  - [ ] Extract to preferred location
  - [ ] Add to PATH (optional)
  - [ ] Verify installation: `jmeter --version`

- [ ] Install Java JDK 11+
  - [ ] Check version: `java -version`
  - [ ] Install if needed
  - [ ] Set JAVA_HOME environment variable

- [ ] Install JMeter Plugins Manager (optional)
  - [ ] Download plugins-manager.jar
  - [ ] Copy to JMETER_HOME/lib/ext/
  - [ ] Install recommended plugins

### Application Preparation
- [ ] Ensure application is running
  - [ ] Start Laravel: `php artisan serve`
  - [ ] Verify: `curl http://localhost:8000`
  - [ ] Check database connection: `php artisan db:show`

- [ ] Prepare test database
  - [ ] Create separate test database (optional but recommended)
  - [ ] Run migrations: `php artisan migrate:fresh`
  - [ ] Seed test data: `php artisan db:seed`

- [ ] Create test users
  - [ ] SuperAdmin: superadmin@pos.com
  - [ ] Business Admin: businessadmin@pos.com
  - [ ] Managers: manager@pos.com, manager1@pos.com, etc.
  - [ ] Cashiers: cashier@pos.com, cashier1-10@pos.com

- [ ] Seed products and inventory
  - [ ] Add 100+ products across categories
  - [ ] Set realistic stock levels
  - [ ] Assign products to branches

- [ ] Configure application for testing
  - [ ] Disable rate limiting (temporarily)
  - [ ] Clear cache: `php artisan cache:clear`
  - [ ] Clear logs: `> storage/logs/laravel.log`
  - [ ] Set APP_DEBUG=false (for realistic performance)

### Test Data Files
- [ ] Verify CSV files exist in jmeter-tests/
  - [ ] users.csv (20+ users with credentials)
  - [ ] products.csv (30+ products)
  - [ ] sale_items.csv (30+ sale scenarios)
  - [ ] customers.csv (10+ customers)
  - [ ] cash_drawer.csv (10+ opening amounts)

---

## 📋 Phase 2: Test Plan Creation

### Create Basic Test Plan (Login Test)
- [ ] Launch JMeter GUI: `jmeter`
- [ ] Add HTTP Request Defaults
  - [ ] Server: localhost
  - [ ] Port: 8000
  - [ ] Protocol: http
- [ ] Add HTTP Cookie Manager
- [ ] Add HTTP Cache Manager (optional)
- [ ] Create Thread Group: "Login_Test"
  - [ ] Set users: 5
  - [ ] Ramp-up: 5 seconds
  - [ ] Loop: 1
- [ ] Add CSV Data Set Config
  - [ ] File: users.csv
  - [ ] Variables: email,password,role,expected_route
- [ ] Add HTTP Request: GET Login Page
  - [ ] Path: /login/${role}
- [ ] Add Regular Expression Extractor (CSRF Token)
  - [ ] Pattern: `<input[^>]*name="_token"[^>]*value="([^"]+)"`
  - [ ] Reference: csrf_token
- [ ] Add HTTP Request: POST Login
  - [ ] Path: /login/${role}
  - [ ] Parameters: email, password, _token, remember
- [ ] Add Response Assertions
  - [ ] Check response code 200 or 302
  - [ ] Check for expected_route in response
- [ ] Add Listeners
  - [ ] View Results Tree (debugging)
  - [ ] Summary Report
  - [ ] Aggregate Report
  - [ ] Response Time Graph
- [ ] Save Test Plan: POS_Login_Test.jmx

### Create Sales Test Plan
- [ ] Duplicate Login Test Plan
- [ ] Rename to: POS_Sales_Test.jmx
- [ ] Filter users to cashiers only
- [ ] Add: Open Cash Drawer request
  - [ ] POST /cash-drawer/open
  - [ ] Parameters: opening_amount, opening_notes
- [ ] Add: Load POS Terminal
  - [ ] GET /terminal
- [ ] Add: Create Sale (Loop Controller: 5 times)
  - [ ] POST /sales
  - [ ] Use sale_items.csv data
  - [ ] Extract sale_id from response
- [ ] Add Think Times (3-5 seconds between requests)
- [ ] Save Test Plan

### Create Full Workflow Test Plan
- [ ] Combine all user journeys
- [ ] Use Random Controller for realistic mix
  - [ ] 50% Cashier workflows (sales)
  - [ ] 25% Manager workflows (reports, inventory)
  - [ ] 20% Business Admin (analytics, staff)
  - [ ] 5% SuperAdmin (system management)
- [ ] Add Throughput Controller for request rate control
- [ ] Save as: POS_FullWorkflow_Test.jmx

---

## 📋 Phase 3: Baseline Testing

### Smoke Test (Verify Functionality)
- [ ] Configure: 1 user, 1 minute
- [ ] Run test in GUI mode
- [ ] Check View Results Tree for errors
- [ ] Verify CSRF token extraction works
- [ ] Verify login successful
- [ ] Verify logout successful
- [ ] Fix any errors before proceeding

### Baseline Performance Test
- [ ] Configure: 5 users, 5 minutes
- [ ] Run in CLI mode
- [ ] Command: `jmeter -n -t POS_Login_Test.jmx -l baseline_results.jtl -e -o baseline_report`
- [ ] Review HTML report
- [ ] Document baseline metrics:
  - [ ] Average response time: _____ ms
  - [ ] 95th percentile: _____ ms
  - [ ] Throughput: _____ req/s
  - [ ] Error rate: _____ %
- [ ] Save results for comparison

---

## 📋 Phase 4: Progressive Load Testing

### Light Load Test
- [ ] Users: 25
- [ ] Duration: 10 minutes
- [ ] Ramp-up: 2 minutes
- [ ] Run test: `./jmeter-tests/run_tests.sh` (option 3)
- [ ] Monitor system resources
- [ ] Review results:
  - [ ] Response time acceptable: < 500ms (95th percentile)
  - [ ] Error rate acceptable: < 0.1%
  - [ ] No database errors
  - [ ] No memory leaks
- [ ] Document findings

### Normal Load Test
- [ ] Users: 50
- [ ] Duration: 10 minutes
- [ ] Ramp-up: 5 minutes
- [ ] Run test (option 4)
- [ ] Monitor:
  - [ ] CPU usage < 70%
  - [ ] Memory usage stable
  - [ ] Database connections < 100
  - [ ] No slow queries
- [ ] Compare to baseline
- [ ] Document degradation

### Peak Load Test
- [ ] Users: 100
- [ ] Duration: 15 minutes
- [ ] Ramp-up: 5 minutes
- [ ] Run test (option 5)
- [ ] Monitor system carefully
- [ ] Watch for:
  - [ ] Response time spikes
  - [ ] Increased error rates
  - [ ] Database connection pool exhaustion
  - [ ] Memory growth
- [ ] Document issues found

---

## 📋 Phase 5: Stress Testing

### Stress Test
- [ ] Users: 200
- [ ] Duration: 20 minutes
- [ ] Ramp-up: 5 minutes
- [ ] Run test (option 6)
- [ ] Expect some degradation
- [ ] Identify bottlenecks:
  - [ ] Database queries
  - [ ] File I/O operations
  - [ ] Session management
  - [ ] Memory usage
- [ ] Document breaking points

### Extreme Stress Test
- [ ] Users: 500
- [ ] Duration: 15 minutes
- [ ] Ramp-up: 2 minutes
- [ ] Run test (option 7)
- [ ] Find system limits
- [ ] Document:
  - [ ] Maximum sustainable users
  - [ ] Response time at peak
  - [ ] Error rate at peak
  - [ ] Recovery time after load

---

## 📋 Phase 6: Specialized Tests

### Spike Test
- [ ] Create test plan with Stepping Thread Group
- [ ] Configuration:
  - [ ] Start: 50 users
  - [ ] Spike to: 300 users (in 30 seconds)
  - [ ] Hold: 5 minutes
  - [ ] Drop to: 50 users (in 30 seconds)
- [ ] Run test
- [ ] Verify system handles sudden load
- [ ] Check recovery speed

### Endurance Test
- [ ] Users: 50 (constant)
- [ ] Duration: 2-4 hours
- [ ] Run test (option 8)
- [ ] Monitor for:
  - [ ] Memory leaks
  - [ ] Session buildup
  - [ ] Log file growth
  - [ ] Database connection leaks
  - [ ] Performance degradation over time
- [ ] Document long-term stability

### Concurrent Sales Test (Critical!)
- [ ] Create specialized test plan
- [ ] 100 cashiers selling SAME products simultaneously
- [ ] Run for 5 minutes
- [ ] Verify:
  - [ ] No negative inventory
  - [ ] No database deadlocks
  - [ ] Correct COGS calculations
  - [ ] All sales recorded
  - [ ] Stock logs accurate
- [ ] This is THE most critical test!

---

## 📋 Phase 7: Monitoring & Analysis

### During Test Monitoring
- [ ] Run monitoring script: `./jmeter-tests/monitor.sh`
- [ ] Watch real-time metrics:
  - [ ] CPU usage
  - [ ] Memory usage
  - [ ] Disk I/O
  - [ ] Network traffic
  - [ ] Database connections
  - [ ] Active threads
- [ ] Watch Laravel logs for errors
- [ ] Use `htop` or `top` for process monitoring

### Post-Test Analysis
- [ ] Review JMeter HTML dashboard
  - [ ] Response Times Over Time graph
  - [ ] Transactions Per Second
  - [ ] Response Time Percentiles
  - [ ] Error Rate
- [ ] Analyze monitoring logs
  - [ ] CPU usage patterns
  - [ ] Memory growth
  - [ ] Database query times
- [ ] Check Laravel logs for exceptions
- [ ] Review MySQL slow query log
- [ ] Calculate key metrics:
  - [ ] Average response time
  - [ ] 95th percentile response time
  - [ ] Throughput (req/s)
  - [ ] Error percentage
  - [ ] Concurrent users supported

---

## 📋 Phase 8: Optimization

### Identify Bottlenecks
- [ ] Database queries
  - [ ] Check for missing indexes
  - [ ] Optimize N+1 query problems
  - [ ] Add query caching
- [ ] File operations
  - [ ] Optimize image uploads
  - [ ] Use cloud storage (S3)
  - [ ] Implement CDN
- [ ] Session management
  - [ ] Switch to Redis for sessions
  - [ ] Implement session cleanup
- [ ] Code optimization
  - [ ] Enable OPcache
  - [ ] Optimize Eloquent queries
  - [ ] Add eager loading

### Apply Optimizations
- [ ] Add database indexes
- [ ] Implement query caching
- [ ] Enable Redis for cache and sessions
- [ ] Optimize controller logic
- [ ] Add queue workers for background jobs
- [ ] Enable HTTP/2 and compression
- [ ] Configure proper connection pooling

### Re-test After Optimizations
- [ ] Run same tests again
- [ ] Compare before/after metrics
- [ ] Document improvements:
  - [ ] Response time improvement: ____%
  - [ ] Throughput increase: ____%
  - [ ] Error rate reduction: ____%
  - [ ] Max users increased: ____

---

## 📋 Phase 9: Reporting

### Create Test Report
- [ ] Executive Summary
  - [ ] Test objectives
  - [ ] Tests performed
  - [ ] Key findings
  - [ ] Recommendations
- [ ] Detailed Results
  - [ ] Performance metrics table
  - [ ] Graphs and charts
  - [ ] Bottlenecks identified
  - [ ] Optimization impact
- [ ] Appendices
  - [ ] Test configurations
  - [ ] Raw data
  - [ ] System specifications

### Document Recommendations
- [ ] Infrastructure scaling
  - [ ] Horizontal vs vertical scaling
  - [ ] Load balancer configuration
  - [ ] Database read replicas
- [ ] Application optimizations
  - [ ] Code changes
  - [ ] Configuration tuning
  - [ ] Caching strategy
- [ ] Monitoring & alerting
  - [ ] Set up performance monitoring
  - [ ] Configure alerts
  - [ ] Establish SLAs

---

## 📋 Phase 10: Continuous Testing

### Establish Baseline
- [ ] Document baseline performance
- [ ] Set performance budgets
- [ ] Define SLAs:
  - [ ] 95% of requests < 500ms
  - [ ] Error rate < 0.1%
  - [ ] Support 200+ concurrent users

### Integrate into CI/CD
- [ ] Add JMeter tests to pipeline
- [ ] Run tests on staging environment
- [ ] Fail build if performance degrades
- [ ] Generate trend reports

### Regular Testing Schedule
- [ ] Weekly: Smoke tests
- [ ] Monthly: Full load tests
- [ ] Quarterly: Stress tests
- [ ] Before major releases: Complete test suite

---

## ✅ Success Criteria

### Functional
- [✓] All API endpoints return correct responses
- [✓] No data corruption under concurrent access
- [✓] All business rules enforced
- [✓] Activity logs capture all actions

### Performance
- [ ] 95% requests < 500ms (normal load)
- [ ] 99% requests < 2s (peak load)
- [ ] Error rate < 0.1% (normal load)
- [ ] Error rate < 1% (peak load)
- [ ] Support 200+ concurrent users
- [ ] No memory leaks over 4 hours

### Reliability
- [ ] System recovers after spike
- [ ] Graceful degradation under stress
- [ ] No database deadlocks
- [ ] No lost transactions

---

## 🎯 Critical Test Scenarios Priority

1. **HIGHEST PRIORITY**
   - [✓] Concurrent sales (same products)
   - [✓] Cash drawer session management
   - [✓] Inventory accuracy under load
   - [✓] CSRF token validation

2. **HIGH PRIORITY**
   - [ ] Database transaction integrity
   - [ ] Activity logging performance
   - [ ] Session management (120 min timeout)
   - [ ] Sales reports with complex queries

3. **MEDIUM PRIORITY**
   - [ ] File uploads (products, bulk imports)
   - [ ] PDF/CSV export generation
   - [ ] Search and filtering performance
   - [ ] Pagination with large datasets

4. **LOW PRIORITY**
   - [ ] Map visualization
   - [ ] Real-time dashboard updates
   - [ ] Email notifications

---

## 📞 Need Help?

**Resources:**
- JMeter Documentation: https://jmeter.apache.org/usermanual/
- Test Plan: `JMETER_STRESS_TEST_PLAN.md`
- Quick Start: `jmeter-tests/README.md`

**Common Issues:**
- CSRF token not extracting → Check regex pattern
- Connection refused → Verify app is running
- Out of memory → Increase JMeter heap size
- Database errors → Check connection pool settings

---

**Remember:** Test incrementally! Start small and gradually increase load. 🚀
