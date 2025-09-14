# Darajat Educational Platform 📚

[![Flutter](https://img.shields.io/badge/Flutter-Framework-blue?logo=flutter)](https://flutter.dev/)
[![Laravel](https://img.shields.io/badge/Laravel-API-red?logo=laravel)](https://laravel.com/)
[![Livewire](https://img.shields.io/badge/Livewire-Dashboard-orange)](https://laravel-livewire.com/)

**Inspired by the Quranic verse:** { يرفع الله الذين آمنوا منكم والذين أوتوا العلم درجات }

Darajat is an innovative educational platform designed to facilitate knowledge sharing and learning through interactive courses, assessments, and community engagement.

## ✨ Features

### 🎓 Learning Experience
- **Video Courses** in various sciences and specialties 💻
- **Interactive Environment** (comments, replies, likes, views, ratings, attachment of explanation files) 👨‍💻
- **Detailed Profile** showcasing expertise, skills, and educational achievements 👤
- **Course Publishing** and knowledge sharing 👨‍🏫
- **Course Categorization** and easy search functionality 🔎
- **Exams and Certificates** delivered via email 📝

### 🎯 User Engagement
- **Notification System** 🔔
- **Motivation Flame** to track continuity and progress 🔥
- **Tasks and Incentive Badges** 🎖
- **OTP Verification** or **Google Authentication** for account creation 🚪

### 💰 Monetization
- **Electronic Payment System** 💸
- **Payment Process Notifications** via email 📨
- **Electronic Wallet** for earnings 👝

### 👨‍💼 Admin Dashboard
- **Course Management** 🔧
- **Content Filtering** for inappropriate material 🫣
- **Publication Request Control** 🖋
- **Platform Statistics** and user information viewing 📈
- **User Banning** for usage policy violations ⛔️

## 🛠 Technology Stack

**Frontend:** Flutter (Hamza Al-Najjar)  
https://github.com/hamza-alnaggar/Darajat.git

**Backend API:** Laravel (Ali Asaad + Omar Al-Dalati)  
**Admin Dashboard:** Laravel + Livewire with Blade (Omar Al-Dalati)

## 🚀 Getting Started

### Prerequisites
- Flutter SDK
- PHP >= 7.4
- Composer
- MySQL

### Installation

1. Clone the repository:
```bash
git clone https://github.com/your-username/darajat-platform.git
cd darajat-platform
```

2. Install backend dependencies:
```bash
cd backend
composer install
```

3. Set up environment configuration:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database settings in the `.env` file

5. Run migrations:
```bash
php artisan migrate
```

6. Install frontend dependencies:
```bash
cd ../frontend
flutter pub get
```

7. Run the application:
```bash
flutter run
```

## 📱 Platform Structure

```
darajat-platform/
├── backend/          # Laravel API
├── frontend/         # Flutter Application
└── dashboard/        # Admin Dashboard (Laravel + Livewire)
```

## 🤝 Contributing

We welcome contributions to Darajat Educational Platform! Please feel free to submit pull requests or open issues for bugs and feature requests.


## 📞 Contact

For questions or support, please contact with us: 
omaraldalati3@gmail.com

---
