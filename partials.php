<?php
// Shared header/footer chrome for all public pages.
require_once __DIR__ . '/lib.php';

function khb_header($title, $active = '') {
    $m = current_member();
    $cartCount = count(cart());
    $nav = [
        'listen.php'  => 'Listen',
        'beats.php'   => 'Beats',
        'book.php'    => 'Book',
        'about.php'   => 'The Collection',
        'contact.php' => 'Contact',
    ];
    echo '<!DOCTYPE html><html lang="en"><head>';
    echo '<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . h($title) . ' — ' . SITE_NAME . '</title>';
    echo '<meta name="description" content="KAOS Hot Beatz — original MPC-crafted instrumentals, studio sessions, and exclusive beats by an MPC and Yamaha collector.">';
    echo '<link rel="stylesheet" href="/assets/style.css">';
    echo '</head><body>';
    echo '<header class="site-head"><div class="wrap">';
    echo '<a class="brand" href="/"><span class="brand-kaos">KAOS</span> HOT BEATZ</a>';
    echo '<button class="nav-toggle" aria-label="Menu" onclick="document.body.classList.toggle(\'nav-open\')">☰</button>';
    echo '<nav class="site-nav">';
    foreach ($nav as $href => $label) {
        $cls = ($active === $href) ? ' class="on"' : '';
        echo '<a' . $cls . ' href="/' . $href . '">' . h($label) . '</a>';
    }
    echo '<a class="cart-link" href="/cart.php">🛒 <span class="cart-count">' . $cartCount . '</span></a>';
    if ($m) echo '<a href="/member/account.php">' . h($m['name']) . '</a>';
    else echo '<a class="btn-sm" href="/member/login.php">Sign In</a>';
    echo '</nav></div></header><main>';
}

function khb_footer() {
    echo '</main><footer class="site-foot"><div class="wrap">';
    echo '<div class="foot-cols">';
    echo '<div><span class="brand"><span class="brand-kaos">KAOS</span> HOT BEATZ</span>';
    echo '<p class="muted">' . h(ARTIST_TAGLINE) . '<br>' . h(ARTIST_GENRES) . '</p>';
    echo '<p style="margin-top:10px"><a href="' . h(BEATSTARS_URL) . '" target="_blank" rel="noopener">BeatStars ↗</a> &nbsp; <a href="' . h(SUNO_URL) . '" target="_blank" rel="noopener">Suno ↗</a> &nbsp; <a href="' . h(INSTAGRAM_URL) . '" target="_blank" rel="noopener">Instagram ↗</a></p></div>';
    echo '<div><h4>Shop</h4><a href="/beats.php">Beats</a><a href="/cart.php">Cart</a><a href="/member/account.php">My Purchases</a></div>';
    echo '<div><h4>Studio</h4><a href="/book.php">Book a Session</a><a href="/about.php">The Collection</a><a href="/contact.php">Contact</a></div>';
    echo '<div><h4>Account</h4><a href="/member/login.php">Sign In</a><a href="/member/register.php">Create Account</a><a href="/licensing.php">Licensing</a></div>';
    echo '</div>';
    echo '<p class="copyright">© ' . date('Y') . ' Omar Huertas LLC. All beats & recordings protected. KAOS HOT BEATZ™.</p>';
    echo '</div></footer>';
    echo '<script src="/assets/main.js"></script></body></html>';
}
