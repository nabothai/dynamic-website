<?php include 'header.php'; ?>
<main class="home-page" style="min-height:70vh;">
    <section class="hero" style="text-align:center; margin:3em 0 2em 0;">
        <h1 style="font-size:2.7rem; color:#c0392b; margin-bottom:0.7em;">Welcome to <span style="color:#27ae60">ZimBites Restaurant!</span></h1>
        <div style="margin: 1em 0; text-align: center;">
            <button onclick="window.history.back()" class="btn">&larr; Back</button>
        </div>
        <p style="font-size:1.2rem; color:#444; margin-bottom:2em;">Delicious Italian food, made fresh every day.<br>Order online or visit us in person!</p>
        <div style="margin-bottom:2em;">
            <a href="menu.php" class="btn btn-primary" style="margin:0 0.5em 0.5em 0.5em; font-size:1.1em;">View Our Menu</a>
            <a href="login.php" class="btn" style="margin:0 0.5em 0.5em 0.5em;">Login</a>
            <a href="register.php" class="btn" style="margin:0 0.5em 0.5em 0.5em;">Register</a>
        </div>
    </section>
        <div style="margin: 1em 0; text-align: center;">
            <button onclick="window.history.back()" class="btn">&larr; Back</button>
        </div>
    </main>
    <section class="highlights" style="max-width:900px; margin:2em auto;">
        <h2 style="text-align:center; color:#c0392b; font-size:2rem; margin-bottom:1.5em;">Popular Dishes</h2>
        <div style="display:flex; flex-wrap:wrap; gap:2em; justify-content:center;">
            <div style="flex:1 1 220px; background:white; padding:1.5em; border-radius:12px; box-shadow:0 2px 10px #0001; min-width:220px; max-width:260px; text-align:center;">
                <h3 style="color:#27ae60; margin-bottom:0.5em;">Margherita Pizza</h3>
                <p style="color:#555;">Classic tomato, mozzarella, and basil.</p>
            </div>
            <div style="flex:1 1 220px; background:white; padding:1.5em; border-radius:12px; box-shadow:0 2px 10px #0001; min-width:220px; max-width:260px; text-align:center;">
                <h3 style="color:#27ae60; margin-bottom:0.5em;">Spaghetti Bolognese</h3>
                <p style="color:#555;">Rich meat sauce over fresh pasta.</p>
            </div>
            <div style="flex:1 1 220px; background:white; padding:1.5em; border-radius:12px; box-shadow:0 2px 10px #0001; min-width:220px; max-width:260px; text-align:center;">
                <h3 style="color:#27ae60; margin-bottom:0.5em;">Tiramisu</h3>
                <p style="color:#555;">Classic Italian dessert with coffee and mascarpone.</p>
            </div>
        </div>
    </section>
</main>
<div style="margin: 1em 0; text-align: center;">
    <button onclick="window.history.back()" class="btn">&larr; Back</button>
</div>
<?php include 'footer.php'; ?>
<script src="scripts/homepage.js"></script>
<link rel="stylesheet" href="styles/homepage.css">