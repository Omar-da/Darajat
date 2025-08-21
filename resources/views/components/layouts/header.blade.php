@props(['title', 'withFooter' => false])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Darajat Edu - {{$title}}</title>
    {{-- main --}}
    <link rel="stylesheet" href="{{asset('css/main/header.css')}}">
    <link rel="stylesheet" href="{{asset('css/main/home.css')}}">
    <link rel="stylesheet" href="{{asset('css/main/profile.css')}}">
    <link rel="stylesheet" href="{{asset('css/main/footer_navbar.css')}}">
    {{-- courses --}}
    <link rel="stylesheet" href="{{asset('css/courses/index.css')}}">
    <link rel="stylesheet" href="{{asset('css/courses/video.css')}}">
    <link rel="stylesheet" href="{{asset('css/courses/show_course.css')}}">
    <link rel="stylesheet" href="{{asset('css/courses/quiz.css')}}">
    <link rel="stylesheet" href="{{asset('css/courses/rejected_courses.css')}}">
    <link rel="stylesheet" href="{{asset('css/courses/pending_courses.css')}}">
    {{-- badges --}}
    <link rel="stylesheet" href="{{asset('css/badges/index.css')}}">
    <link rel="stylesheet" href="{{asset('css/badges/show.css')}}">
    <link rel="stylesheet" href="{{asset('css/badges/create.css')}}">
    <link rel="stylesheet" href="{{asset('css/badges/edit.css')}}">
    {{-- users --}}
    <link rel="stylesheet" href="{{asset('css/users/index.css')}}">
    <link rel="stylesheet" href="{{asset('css/users/show.css')}}">
    <link rel="stylesheet" href="{{asset('css/users/followed_course.css')}}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="{{ asset('img/icons/DarajatTrans.png') }}" sizes="192x192" type="image/png">
</head>
<body>
    @use('App\Enums\TypeEnum')

    <nav class="navbar">
        <!-- Platform Name -->
        <div class="brand">
            <span class="gradient-text"><img src="{{asset('img/icons/DarajatTrans.png')}}" alt="logo"></span>
        </div>

        <!-- Navigation Links -->
        <ul class="nav-menu">
            <li><a href="{{route('home')}}" class="nav-link">Home</a></li>
            <li><a href="{{route('courses.cates_and_topics')}}" class="nav-link">Courses</a></li>
        <li><a href="{{route('users.index', ['type' => TypeEnum::USER])}}" class="nav-link">Users</a></li>
            <li><a href="{{route('users.index', ['type' => TypeEnum::TEACHER])}}" class="nav-link">Teachers</a></li>
            <li><a href="{{route('badges.index')}}" class="nav-link">Badges</a></li>
        </ul>

        <!-- Profile Dropdown -->
        <div class="profile-menu">
            <button class="profile-toggler">
                <div class="hamburger">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
            </button>
            
            <div class="dropdown-content">
                <a href="{{route('profile.show')}}" class="dropdown-item">
                    <img class="item-icon profile-icon" src="{{asset('img/icons/profile_icon.png')}}" alt="profile icon"></img>
                    Profile
                </a>
                <!-- Logout Button -->
                <form action="{{route('dashboard.logout')}}" method="POST">
                    @csrf    
                    <button class="dropdown-item logout-btn">
                        <img class="item-icon logout-icon" src="{{asset('img/icons/logout_icon.png')}}" alt="logout icon"></img>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="br"></div>

    <main class="main-content">
        {{ $slot }}
    </main>

    @if($withFooter)
        <x-layouts.back-button/>
    @endif
    
    <x-layouts.footer-navbar/>
</body>
</html>