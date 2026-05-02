# 📊 TradeJournal: The Ultimate Multi-Account Trading Suite

**TradeJournal** is a professional, high-performance trading companion designed for modern traders. Built with a focus on speed, analytics, and precision, it empowers you to manage multiple trading accounts, track performance with deep analytics, and master risk management with an integrated suite of calculators.

![Dashboard Preview](public/images/readme/dashboard.png)

---

## 🚀 Key Features

### 📈 Comprehensive Analytics Dashboard
- **Equity Curve & Performance Tracking**: Visualize your growth with dynamic charts and real-time P&L tracking.
- **Deep Metrics**: Monitor Win Rate, Profit Factor, Expectancy, and Average R:R at a glance.
- **Trade Insights**: Automatically breakdown your best and worst trading days and identify winning patterns.

### 💼 Multi-Account Architecture
- **Isolated Journals**: Manage different strategies or prop firm accounts independently within a single interface.
- **Capital Management**: Track independent balances and performance history for each account.

### 🧮 Professional Trading Tools
- **Advanced Position Sizer**: Calculate optimal lot sizes with a unique **Pips / Price toggle** (optimized for Gold, Forex, Indices, and Crypto).
- **Risk:Reward Calculator**: Instantly verify your trade setups to ensure you're maintaining a healthy edge.
- **Profit/Loss Simulator**: Forecast your earnings by entering pip targets or specific price levels.
- **Drawdown Recovery Tool**: Calculate the exact gain needed to recover from any losing streak.

![Trading Tools Preview](public/images/readme/tools.png)

### 💎 Premium Dark-Themed UI
- **Funding Pips Aesthetic**: A sleek, "hedge-fund" inspired dark interface designed for long trading sessions.
- **Fully Responsive**: Log trades and check analytics seamlessly across desktop and mobile.

---

## 🛠️ Tech Stack
- **Backend**: Laravel 11 (PHP)
- **Frontend**: Blade, Alpine.js, TailwindCSS
- **Database**: SQLite / MySQL
- **Real-time Stats**: Custom Caching Engine for instant analytics

---

## 🚦 Installation & Setup Guide

### 🐧 Linux (Ubuntu/Debian)
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2+ and extensions
sudo apt install -y php8.2 php8.2-curl php8.2-xml php8.2-zip php8.2-sqlite3 php8.2-mbstring

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js & NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Setup Project
git clone https://github.com/ASHLIN-BIJU/Trading-Journal.git
cd Trading-Journal
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run build
php artisan serve
```

### 🪟 Windows
1. Install **XAMPP** or **Wand** (ensure PHP 8.2+).
2. Install **Composer** from [getcomposer.org](https://getcomposer.org/).
3. Install **Node.js** from [nodejs.org](https://nodejs.org/).
4. Open Terminal/CMD:
```powershell
git clone https://github.com/ASHLIN-BIJU/Trading-Journal.git
cd Trading-Journal
composer install
npm install
copy .env.example .env
php artisan key:generate
# Create an empty file at database\database.sqlite
php artisan migrate
npm run build
php artisan serve
```

### 🍎 macOS
```bash
# Install Homebrew if not installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install PHP and Node
brew install php node composer

# Setup Project
git clone https://github.com/ASHLIN-BIJU/Trading-Journal.git
cd Trading-Journal
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run build
php artisan serve
```

---

## 🧪 Demo Data (Optional)
If you want to test the application with sample data (like in the screenshots), run:
```bash
php artisan migrate:fresh --seed
```
*Note: This will create a demo user: `demo@tradejournal.com` with password `password`.*

---

## 🎯 Optimization & SEO Keywords
`Trading Journal`, `Multi-Account Tracker`, `Forex Risk Management`, `Gold Position Sizer`, `Trade Analytics Dashboard`, `XAUUSD Calculator`, `Prop Firm Journal`, `Trading Performance Tracker`.

---

## 🖥️ Linux Desktop App (One-Click Startup)
For a native app experience on Linux, you can use the built-in launcher:
1. Make the launcher executable:
   ```bash
   chmod +x launch.sh
   ```
2. Create a desktop shortcut (optional):
   - You can copy the provided `Trading-Journal.desktop` (template) to your desktop.
   - Update the `Exec` and `Icon` paths to point to your project folder.
   - Right-click the icon and select **"Allow Launching"**.

---

Developed with ❤️ by [Ashlin Biju](https://github.com/ASHLIN-BIJU).
