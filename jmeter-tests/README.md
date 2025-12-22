# JMeter Stress Testing - Quick Start Guide

## 📦 Prerequisites

### Required Software
1. **Apache JMeter 5.6+**
   ```bash
   # Download from https://jmeter.apache.org/download_jmeter.cgi
   # Or install via package manager:
   
   # macOS
   brew install jmeter
   
   # Ubuntu/Debian
   sudo apt update
   sudo apt install jmeter
   
   # Or download and extract manually
   wget https://dlcdn.apache.org//jmeter/binaries/apache-jmeter-5.6.3.tgz
   tar -xzf apache-jmeter-5.6.3.tgz
   cd apache-jmeter-5.6.3/bin
   ```

2. **Java JDK 11+**
   ```bash
   # Check Java version
   java -version
   
   # Install if needed (Ubuntu)
   sudo apt install openjdk-11-jdk
   
   # macOS
   brew install openjdk@11
   ```

3. **Application Running**
   - Ensure your Laravel application is running: `php artisan serve`
   - Or configure web server (Nginx/Apache)

---

## 🚀 Quick Start (First Test in 5 Minutes)

### Step 1: Verify Setup
```bash
# Navigate to project root
cd /home/iddrissmus/Projects/pos-supermarket

# Ensure application is running
php artisan serve

# In another terminal, verify it's accessible
curl http://localhost:8000
```

### Step 2: Launch JMeter
```bash
# GUI Mode (for test plan creation and debugging)
jmeter

# Or specify the test plan directly
jmeter -t jmeter-tests/POS_Supermarket_LoadTest.jmx
```

### Step 3: Configure Test Plan
1. Open JMeter GUI
2. **File → Open** → Navigate to `jmeter-tests/` folder
3. We'll create the test plan structure together

---

## 🏗️ Building Your First Test Plan

### Test 1: Simple Login Test (Baseline)

#### Create Test Plan Structure:
```
Test Plan: POS_Baseline_Test
│
├── HTTP Request Defaults
│   Server: localhost
│   Port: 8000
│   Protocol: http
│
├── HTTP Cookie Manager
│
├── Thread Group: Login_Test
│   Number of Threads: 5
│   Ramp-up Period: 5 seconds
│   Loop Count: 1
│   │
│   ├── CSV Data Set Config
│   │   Filename: jmeter-tests/users.csv
│   │   Variable Names: email,password,role,expected_route
│   │
│   ├── HTTP Request: Get Login Page
│   │   Method: GET
│   │   Path: /login/${role}
│   │   │
│   │   └── Regular Expression Extractor: Extract CSRF Token
│   │       Reference Name: csrf_token
│   │       Regular Expression: <input type="hidden" name="_token" value="(.+?)">
│   │       Template: $1$
│   │       Match No: 1
│   │
│   ├── HTTP Request: Submit Login
│   │   Method: POST
│   │   Path: /login/${role}
│   │   Parameters:
│   │     - email: ${email}
│   │     - password: ${password}
│   │     - _token: ${csrf_token}
│   │     - remember: 0
│   │   │
│   │   └── Response Assertion
│   │       Pattern to Test: ${expected_route}
│   │       Response Code: 200 or 302
│   │
│   └── HTTP Request: Logout
│       Method: POST
│       Path: /logout
│       Parameters:
│         - _token: ${csrf_token}
│
└── Listeners
    ├── View Results Tree (for debugging)
    ├── Summary Report
    └── Graph Results
```

---

## 📝 Step-by-Step: Create Login Test in JMeter GUI

### 1. Add HTTP Request Defaults
- Right-click **Test Plan** → **Add** → **Config Element** → **HTTP Request Defaults**
- Set:
  - Server Name: `localhost`
  - Port Number: `8000`
  - Protocol: `http`

### 2. Add HTTP Cookie Manager
- Right-click **Test Plan** → **Add** → **Config Element** → **HTTP Cookie Manager**
- Leave defaults (this manages session cookies)

### 3. Add Thread Group
- Right-click **Test Plan** → **Add** → **Threads** → **Thread Group**
- Name: `Login_Test`
- Set:
  - Number of Threads (users): `5`
  - Ramp-up period (seconds): `5`
  - Loop Count: `1`

### 4. Add CSV Data Set Config
- Right-click **Thread Group** → **Add** → **Config Element** → **CSV Data Set Config**
- Set:
  - Filename: `${__P(user.dir)}/jmeter-tests/users.csv`
  - Variable Names: `email,password,role,expected_route`
  - Delimiter: `,`
  - Recycle on EOF: `True`
  - Stop thread on EOF: `False`
  - Sharing mode: `All threads`

### 5. Add HTTP Request: Get Login Page
- Right-click **Thread Group** → **Add** → **Sampler** → **HTTP Request**
- Name: `GET Login Page`
- Set:
  - Method: `GET`
  - Path: `/login/${role}`

### 6. Add Regular Expression Extractor (CSRF Token)
- Right-click **GET Login Page** → **Add** → **Post Processors** → **Regular Expression Extractor**
- Set:
  - Reference Name: `csrf_token`
  - Regular Expression: `<input[^>]*name="_token"[^>]*value="([^"]+)"`
  - Template: `$1$`
  - Match No: `1`
  - Default Value: `TOKEN_NOT_FOUND`

### 7. Add HTTP Request: Submit Login
- Right-click **Thread Group** → **Add** → **Sampler** → **HTTP Request**
- Name: `POST Login`
- Set:
  - Method: `POST`
  - Path: `/login/${role}`
  - Parameters (click Add button for each):
    ```
    Name: email       | Value: ${email}
    Name: password    | Value: ${password}
    Name: _token      | Value: ${csrf_token}
    Name: remember    | Value: 0
    ```
  - Implementation: `HttpClient4`
  - Follow Redirects: `☑ Checked`

### 8. Add Response Assertion
- Right-click **POST Login** → **Add** → **Assertions** → **Response Assertion**
- Set:
  - Apply to: `Main sample only`
  - Response Field: `Response Code`
  - Pattern Matching Rules: `Contains`
  - Patterns to Test: `200` (click Add)

### 9. Add Listeners
- Right-click **Thread Group** → **Add** → **Listener** → **View Results Tree**
- Right-click **Thread Group** → **Add** → **Listener** → **Summary Report**
- Right-click **Thread Group** → **Add** → **Listener** → **Aggregate Report**

### 10. Save Test Plan
- **File** → **Save Test Plan as...**
- Save to: `jmeter-tests/POS_Login_Test.jmx`

---

## ▶️ Running Tests

### GUI Mode (Debugging & Development)
```bash
# Start JMeter GUI
jmeter -t jmeter-tests/POS_Login_Test.jmx

# Then click the green "Start" button (▶️) in toolbar
```

### CLI Mode (Production Testing)
```bash
# Run test in non-GUI mode (recommended for actual load testing)
jmeter -n -t jmeter-tests/POS_Login_Test.jmx -l results/login_test_results.jtl -e -o results/login_test_report

# Parameters explained:
# -n : Non-GUI mode
# -t : Test plan file
# -l : Results log file
# -e : Generate dashboard report
# -o : Output folder for report

# View results
open results/login_test_report/index.html
# Or: firefox results/login_test_report/index.html
```

### Distributed Testing (Multiple Machines)
```bash
# On master machine
jmeter -n -t test.jmx -R server1,server2,server3 -l results.jtl

# Configure jmeter.properties on master:
# remote_hosts=192.168.1.10,192.168.1.11,192.168.1.12
```

---

## 🧪 Progressive Testing Approach

### Phase 1: Smoke Test (Verify Basics)
```bash
# Test with 1 user to verify everything works
jmeter -n \
  -t jmeter-tests/POS_Login_Test.jmx \
  -Jusers=1 \
  -Jduration=60 \
  -l results/smoke_test.jtl

# Check for errors
grep "false" results/smoke_test.jtl
```

### Phase 2: Load Test (Normal Load)
```bash
# 50 users over 10 minutes
jmeter -n \
  -t jmeter-tests/POS_FullWorkflow_Test.jmx \
  -Jusers=50 \
  -Jrampup=600 \
  -Jduration=600 \
  -l results/load_test_50users.jtl \
  -e -o results/load_test_50users_report
```

### Phase 3: Stress Test (Peak Load)
```bash
# 200 users
jmeter -n \
  -t jmeter-tests/POS_FullWorkflow_Test.jmx \
  -Jusers=200 \
  -Jrampup=300 \
  -Jduration=1800 \
  -l results/stress_test_200users.jtl \
  -e -o results/stress_test_200users_report
```

### Phase 4: Spike Test
```bash
# Sudden load increase: 50 → 300 → 50
# Configure in test plan with Stepping Thread Group plugin
```

---

## 📊 Analyzing Results

### Key Metrics to Check

1. **Response Time**
   ```bash
   # 95th Percentile Response Time
   awk -F',' 'NR>1 {print $2}' results.jtl | sort -n | awk 'BEGIN{c=0} {total[c]=$1; c++;} END{print total[int(c*0.95-0.5)]}'
   ```

2. **Error Rate**
   ```bash
   # Calculate error percentage
   total_requests=$(tail -n +2 results.jtl | wc -l)
   failed_requests=$(grep -c "false" results.jtl)
   error_rate=$(echo "scale=2; $failed_requests / $total_requests * 100" | bc)
   echo "Error Rate: $error_rate%"
   ```

3. **Throughput**
   ```bash
   # Requests per second
   grep -c "^[0-9]" results.jtl | awk -v duration=600 '{print $1/duration " req/s"}'
   ```

### HTML Dashboard Report
Open the generated report:
```bash
# The report includes:
# - Response Times Over Time
# - Transactions per Second
# - Response Time Percentiles
# - Active Threads Over Time
# - Error Rate
open results/load_test_report/index.html
```

---

## 🎯 Test Scenarios Priority

### Start with these tests (in order):

1. ✅ **Login Test** (5 users)
   - Verify authentication works
   - CSRF token extraction
   - Session management

2. ✅ **POS Sales Test** (10 cashiers)
   - Cash drawer opening
   - Product catalog loading
   - Sale creation (1-5 items)
   - Inventory deduction

3. ✅ **Concurrent Sales Test** (50 cashiers)
   - Test database locking
   - Verify inventory accuracy
   - Check for deadlocks

4. ✅ **Report Generation Test** (20 managers)
   - Sales reports with filters
   - CSV/PDF exports
   - Product analytics

5. ✅ **Mixed Load Test** (100 users)
   - All roles active simultaneously
   - Realistic workflow distribution

---

## 🐛 Debugging Tips

### Enable Debug Logging
```bash
# Edit jmeter.properties
log_level.jmeter=DEBUG

# Or set in command line
jmeter -Jjmeter.loglevel=DEBUG -t test.jmx
```

### Common Issues

#### Issue 1: CSRF Token Not Found
**Solution**: Check Regular Expression Extractor pattern
```bash
# Test regex pattern in JMeter:
# View Results Tree → Response Data → RegEx Tester
# Pattern: <input[^>]*name="_token"[^>]*value="([^"]+)"
```

#### Issue 2: 419 CSRF Token Mismatch
**Solution**: Ensure cookies are managed properly
- Add HTTP Cookie Manager
- Enable "Follow Redirects" in HTTP Request
- Extract token from correct page (GET before POST)

#### Issue 3: Connection Refused
**Solution**: Verify app is running
```bash
# Check Laravel app
curl http://localhost:8000

# Check PHP processes
ps aux | grep php
```

#### Issue 4: Out of Memory
**Solution**: Increase JMeter heap size
```bash
# Edit jmeter.sh or jmeter.bat
# Set: HEAP="-Xms1g -Xmx4g -XX:MaxMetaspaceSize=256m"
export HEAP="-Xms2g -Xmx8g"
jmeter -n -t test.jmx
```

---

## 📈 Performance Baseline (Expected Results)

### Normal Load (50 users)
- Response Time (avg): < 300ms
- Response Time (95%): < 500ms
- Throughput: 100+ req/s
- Error Rate: < 0.1%

### Peak Load (200 users)
- Response Time (avg): < 800ms
- Response Time (95%): < 2000ms
- Throughput: 200+ req/s
- Error Rate: < 1%

### Stress Test (500 users)
- Identify breaking point
- Document degradation pattern
- Note: Some failures expected

---

## 🔧 JMeter Plugins (Optional but Recommended)

### Install Plugins Manager
```bash
# Download Plugins Manager JAR
cd $JMETER_HOME/lib/ext
wget https://jmeter-plugins.org/get/ -O jmeter-plugins-manager.jar

# Restart JMeter
# Go to: Options → Plugins Manager
```

### Recommended Plugins
1. **Custom Thread Groups** - For complex load patterns (spike, step)
2. **PerfMon** - Server monitoring (CPU, memory)
3. **Response Times Over Time** - Better visualizations
4. **Throughput Shaping Timer** - Control request rate

---

## 📋 Pre-Test Checklist

- [ ] Application is running (`php artisan serve` or web server)
- [ ] Database has test data (run seeders)
- [ ] Test users exist in database
- [ ] Products and branches are seeded
- [ ] Laravel cache is cleared (`php artisan cache:clear`)
- [ ] Laravel logs are cleared (`> storage/logs/laravel.log`)
- [ ] Database queries are being logged (optional)
- [ ] System resources are monitored (htop, Activity Monitor)
- [ ] Test data CSV files are present in `jmeter-tests/`
- [ ] JMeter is configured correctly (Java version, heap size)

---

## 🎬 Let's Start Testing!

**Recommended First Test:**
```bash
# 1. Start your Laravel app
php artisan serve

# 2. Open another terminal and run baseline test
cd /home/iddrissmus/Projects/pos-supermarket
jmeter -t jmeter-tests/POS_Login_Test.jmx

# 3. Click the green Start button in JMeter GUI
# 4. Watch the "View Results Tree" listener for results
# 5. Check "Summary Report" for overall statistics
```

**Next Steps:**
1. Review the comprehensive test plan: `JMETER_STRESS_TEST_PLAN.md`
2. Create the login test plan following steps above
3. Run baseline test with 5 users
4. Progressively increase load: 10 → 25 → 50 → 100 users
5. Document results at each stage
6. Identify bottlenecks and optimize

---

## 📞 Need Help?

- **JMeter Documentation**: https://jmeter.apache.org/usermanual/
- **Video Tutorial**: Search "JMeter Tutorial" on YouTube
- **Community**: JMeter Users Mailing List

---

**Happy Testing! 🚀**
