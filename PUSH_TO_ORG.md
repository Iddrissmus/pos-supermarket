# Push to Organization Repository

## Current Setup
- **Personal Repo**: `origin` → `https://github.com/Iddrissmus/pos-supermarket.git`
- **Organization Repo**: (to be added)

---

## Option 1: Keep Both Repos (Recommended)

Add organization repo as a second remote. You can push to both repos independently.

### Steps:

1. **Add organization remote**:
   ```bash
   git remote add org https://github.com/YOUR-ORG/pos-supermarket.git
   ```
   Replace `YOUR-ORG` with your organization name.

2. **Verify remotes**:
   ```bash
   git remote -v
   ```
   You should see:
   - `origin` → your personal repo
   - `org` → organization repo

3. **Push to organization repo**:
   ```bash
   git push org main
   ```

4. **Future pushes**:
   - To personal repo: `git push origin main`
   - To organization repo: `git push org main`
   - To both: `git push origin main && git push org main`

---

## Option 2: Change Origin to Organization Repo

Replace your current origin with the organization repo.

### Steps:

1. **Remove current origin**:
   ```bash
   git remote remove origin
   ```

2. **Add organization repo as origin**:
   ```bash
   git remote add origin https://github.com/YOUR-ORG/pos-supermarket.git
   ```

3. **Push to new origin**:
   ```bash
   git push -u origin main
   ```

**Note**: This removes your personal repo from remotes. You can add it back later if needed:
```bash
git remote add personal https://github.com/Iddrissmus/pos-supermarket.git
```

---

## Option 3: Push to Both at Once

If you want to push to both repos simultaneously:

1. **Add organization remote** (if not already added):
   ```bash
   git remote add org https://github.com/YOUR-ORG/pos-supermarket.git
   ```

2. **Push to both**:
   ```bash
   git push origin main && git push org main
   ```

Or create an alias for convenience:
```bash
git config alias.pushall '!git push origin main && git push org main'
```

Then use: `git pushall`

---

## Important Notes

- **Before pushing**: Make sure the organization repo exists and you have push access
- **First push**: You may need to use `git push -u org main` to set upstream tracking
- **Authentication**: Ensure you're authenticated with GitHub (SSH keys or personal access token)
- **Branch name**: If organization repo uses `master` instead of `main`, adjust accordingly:
  ```bash
  git push org main:master
  ```

---

## Verify Your Setup

After adding the remote, verify:
```bash
git remote -v
```

You should see both remotes listed.

