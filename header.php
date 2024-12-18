<header class="site-header">
    <div class="header-container">
        <!-- Logo and brand section -->
        <div class="brand-section">
            <a href="/home.php" class="brand-link">
                <span class="brand-name">Blog CMS</span>
            </a>
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
                <span class="menu-icon"></span>
            </button>
        </div>

        <!-- Navigation section -->
        <nav class="main-nav" id="mainNav">
            <ul class="nav-list">
                <?php if (!isset($_SESSION["userid"]) || in_array($_SESSION["role"], ["admin", "editor"])): ?>
                    <li class="nav-item"><a href="/admin.php">Admin</a></li>
                <?php endif; ?>
                <li class="nav-item"><a href="/blog.php">Blog</a></li>
                <li class="nav-item"><a href="/about.php">About</a></li>
                <li class="nav-item dropdown">
                    <a href="#" class="dropdown-trigger">More</a>
                    <ul class="dropdown-menu">
                        <li><a href="/contact.php">Contact</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- User section -->
        <div class="user-section">
            <?php if (isset($_SESSION["userid"])): ?>
                <div class="user-menu">
                    <button class="user-menu-trigger">
                        <img src="/img/lego_user.png" alt="Profile" class="user-avatar">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    </button>
                    <ul class="user-dropdown">
                        <li><a href="/profile.php"><i class="fa-solid fa-user"></i>Profile</a></li>
                        <li><a href="/includes/logout.inc.php"><i class="fa-solid fa-right-from-bracket"></i>Log Out</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="/login.php" class="sign-in-button">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumbs-container">
        <ul class="breadcrumb-list">
            <li><a href="/home.php"><i class="fa-solid fa-house"></i></a></li>
            <li id="breadcrumbs-category"><a href="#"></a></li>
            <li id="breadcrumbs-page"></li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mainNav = document.getElementById('mainNav');
    
    mobileMenuToggle.addEventListener('click', () => {
        mainNav.classList.toggle('active');
        mobileMenuToggle.classList.toggle('active');
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.main-nav') && !e.target.closest('.mobile-menu-toggle')) {
            mainNav.classList.remove('active');
            mobileMenuToggle.classList.remove('active');
        }
    });

    // Dropdown functionality
    const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
    
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const dropdown = trigger.closest('.dropdown');
            dropdown.classList.toggle('active');
        });
    });
});
</script>