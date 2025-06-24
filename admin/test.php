<section class="login-section">
    <div class="login-card">
        <div class="logo">
            <img src="https://i.postimg.cc/sxNCrL6d/logo-white-03.png" alt="شعار" class="logo-image">
            <div>
                <div class="logo-text">دليل البرامج الصيفية</div>
                <div class="logo-subtext">للفتيات في الرياض 1447هـ</div>
            </div>
        </div>
        <h2>تسجيل دخول الأدمن 🔒</h2>
        <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> اسم المستخدم</label>
                <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> كلمة المرور</label>
                <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
            </div>
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> تذكرني
                </label>
                <a href="forgot_password.php" class="forgot-password">نسيت كلمة المرور؟</a>
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </button>
        </form>
    </div>
</section>

<style>
.login-section {
    max-width: 450px;
    margin: 40px auto;
    animation: fadeIn 0.8s ease-out;
}

.login-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    text-align: center;
    transform: scale(1);
    transition: transform 0.3s ease;
}

.login-card:hover {
    transform: scale(1.02);
}

.login-card .logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    margin-bottom: 1.5rem;
}

.logo-image {
    width: 100px;
    height: 100px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.logo-subtext {
    font-size: 0.85rem;
    color: #666;
}

.login-card h2 {
    color: var(--primary);
    font-size: 1.6rem;
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 10px;
}

.login-card h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: var(--secondary);
    border-radius: 2px;
}

.error-message {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--secondary);
    font-size: 0.9rem;
    margin-bottom: 1rem;
    padding: 10px;
    background: #fff0f0;
    border-radius: 8px;
    animation: slideIn 0.5s ease-out;
}

.login-form {
    display: grid;
    gap: 1rem;
}

.form-group {
    text-align: right;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: var(--dark);
    margin-bottom: 5px;
}

.form-group i {
    color: var(--primary);
}

.login-form input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 0.95rem;
    outline: none;
    transition: all 0.3s ease;
}

.login-form input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 8px rgba(138, 43, 226, 0.2);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    margin: 10px 0;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 5px;
    color: var(--dark);
}

.forgot-password {
    color: var(--primary);
    text-decoration: none;
}

.forgot-password:hover {
    text-decoration: underline;
}

.login-btn {
    background: var(--primary);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.login-btn:hover {
    background: #7a1fc2;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

@media (max-width: 576px) {
    .login-section {
        margin: 20px;
        padding: 15px;
    }

    .login-card {
        padding: 1.5rem;
    }

    .logo-image {
        width: 80px;
        height: 80px;
    }

    .logo-text {
        font-size: 1.2rem;
    }

    .logo-subtext {
        font-size: 0.75rem;
    }

    .login-card h2 {
        font-size: 1.4rem;
    }

    .login-form input {
        padding: 10px;
        font-size: 0.9rem;
    }

    .login-btn {
        padding: 10px;
        font-size: 0.9rem;
    }
}
</style>