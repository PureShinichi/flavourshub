const state = { index: {}, cart: [] };
// Define exact order for seamless navigation
const categories = ['burgers', 'pizzas', 'pastas', 'coffee', 'frappe', 'fruity', 'ice_cream', 'cakes', 'bingsu', 'pastries'];
const drinks = ['coffee', 'frappe', 'fruity'];
const desserts = ['ice_cream', 'cakes', 'bingsu', 'pastries'];

document.addEventListener('DOMContentLoaded', () => {
  categories.forEach(c => state.index[c] = 0);
  showCategory('burgers');
  const bToggle = document.getElementById('basket-toggle');
  if (bToggle) bToggle.onclick = toggleBasket;

  // First-Time User Welcome
  if (!localStorage.getItem('flame_welcome_shown')) {
    setTimeout(() => {
      showPopup("🔥 WELCOME TO FLAVOURS HUB! Use code FLAME-FIRST for RM5 OFF your first order!");
      localStorage.setItem('flame_welcome_shown', 'true');
    }, 2000);
  }
});

function toggleDrinks() {
  const el = document.getElementById('drinks-submenu');
  const btn = document.getElementById('btn-drinks');

  // Toggle Logic
  if (el.style.display === 'none' || !el.style.display || el.style.display === '') {
    // Open Drinks
    showCategory('coffee'); // Default to first drink
    document.getElementById('menu').scrollIntoView({ behavior: 'smooth' });
  } else {
    // Close Drinks -> Go back to main
    el.style.display = 'none';
    btn.classList.remove('active');
    showCategory('burgers');
  }
}

function toggleDesserts() {
  const el = document.getElementById('desserts-submenu');
  const btn = document.getElementById('btn-desserts');

  if (el.style.display === 'none' || !el.style.display || el.style.display === '') {
    // Open Desserts
    showCategory('ice_cream');
    document.getElementById('menu').scrollIntoView({ behavior: 'smooth' });
  } else {
    // Close Desserts
    el.style.display = 'none';
    btn.classList.remove('active');
    showCategory('burgers');
  }
}

function showCategory(cat) {
  // Hide all carousels
  document.querySelectorAll('.carousel-container').forEach(e => e.style.display = 'none');

  // Reset Navigation Active States
  document.querySelectorAll('.category-btn').forEach(e => e.classList.remove('active'));
  document.getElementById('drinks-submenu').style.display = 'none';
  document.getElementById('desserts-submenu').style.display = 'none';

  // Show Target
  const target = document.getElementById(cat);
  if (target) {
    target.style.display = 'flex';
    updateCarousel(cat);
    // Scroll to menu to keep focus
    // document.getElementById('menu').scrollIntoView({ behavior: 'smooth' }); // Optional: removed to prevent annoying jumps if user is just clicking around
  }

  // Handle Parents (Drinks/Desserts)
  if (drinks.includes(cat)) {
    document.getElementById('drinks-submenu').style.display = 'flex';
    document.getElementById('btn-drinks').classList.add('active');
    // Highlight specific drink sub-button
    const subBtn = document.getElementById('btn-' + cat);
    if (subBtn) subBtn.classList.add('active');

  } else if (desserts.includes(cat)) {
    document.getElementById('desserts-submenu').style.display = 'flex';
    document.getElementById('btn-desserts').classList.add('active');
    // Highlight specific dessert sub-button
    const subBtn = document.getElementById('btn-' + cat);
    if (subBtn) subBtn.classList.add('active');

  } else {
    // Main categories
    const btn = document.getElementById('btn-' + cat);
    if (btn) btn.classList.add('active');
  }
}

function updateCarousel(cat) {
  const el = document.getElementById(cat);
  if (!el) return;
  const track = el.querySelector('.carousel-track');
  const cards = el.querySelectorAll('.carousel-card');

  // Card width 280 + 30 gap = 310
  const w = 310;
  const idx = state.index[cat];

  track.style.transform = `translateX(${-idx * w}px)`;

  cards.forEach((c, i) => {
    c.className = 'carousel-card'; // reset
    c.style.opacity = '0.3';
    if (i === idx) {
      c.classList.add('active');
      c.style.opacity = '1';
    } else if (i === idx - 1) {
      c.classList.add('prev');
      c.style.opacity = '0.7';
    } else if (i === idx + 1) {
      c.classList.add('next');
      c.style.opacity = '0.7';
    }
  });
}

function move(cat, dir) {
  const cards = document.querySelectorAll('#' + cat + ' .carousel-card');
  let idx = state.index[cat] + dir;

  // Continuous Navigation Logic
  if (idx >= cards.length) {
    // Go to Next Category
    const currIdx = categories.indexOf(cat);
    if (currIdx < categories.length - 1) {
      const nextCat = categories[currIdx + 1];
      showCategory(nextCat);
    }
    return;
  }

  if (idx < 0) {
    // Go to Prev Category -> Go to last item of prev category ideally, but simplifying to just show category
    const currIdx = categories.indexOf(cat);
    if (currIdx > 0) {
      const prevCat = categories[currIdx - 1];
      showCategory(prevCat);
      // Optional: Set to last index of prev category for smooth reverse flow
      // state.index[prevCat] = document.querySelectorAll('#'+prevCat+' .carousel-card').length - 1;
      // updateCarousel(prevCat);
    }
    return;
  }

  state.index[cat] = idx;
  updateCarousel(cat);
}

/* API LOGIC */
async function api(form) {
  try {
    const res = await fetch('order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: form
    });
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      console.error("JSON Error:", text);
      return { error: 'Server Error' };
    }
  } catch (e) {
    console.error(e);
    return { error: 'Network Error' };
  }
}

let surpriseShown = false;

function showSurprisePopup() {
  if (surpriseShown) return;
  const p = document.getElementById('surprise-popup');
  if (p) {
    p.classList.add('show');
    surpriseShown = true;
    // Play a subtle sound or trigger haptic if possible (omitted for web)
    console.log("SURPRISE! 15% DISCOUNT UNLOCKED!");
  }
}

function closeSurprisePopup() {
  const p = document.getElementById('surprise-popup');
  if (p) {
    p.classList.remove('show');
    showPopup("15% Discount Applied to your total! 🎁");
  }
}

async function addToCart(food, cat, img) {
  const res = await api(`action=add&food=${encodeURIComponent(food)}&category=${cat}&img=${encodeURIComponent(img)}`);
  if (res.error) {
    showPopup(res.error);
  } else {
    renderBasket(res);
    showPopup("Added to Basket!");
    animateBasketBtn();
  }
}

async function removeItem(food) {
  const res = await api(`action=remove&food=${encodeURIComponent(food)}`);
  renderBasket(res);
}

async function confirmPay() {
  const btn = document.querySelector('.pay-btn-pro');
  btn.innerText = 'Processing...';
  btn.disabled = true;

  const res = await api(`action=confirm&voucher=${encodeURIComponent(appliedVoucher)}`);

  if (res.status === 'confirmed') {
    renderBasket([]);
    closeCheckout();
    toggleBasket(); // hide sidebar

    let msg = `Order Confirmed! +${res.points} pts. Badge: ${res.new_badge}`;
    if (res.voucher_discount > 0) {
      msg = `VOUCHER APPLIED! RM${res.voucher_discount.toFixed(2)} saved! ` + msg;
    }
    if (res.surprise_discount > 0) {
      msg += ` + Surge Discount!`;
    }

    showPopup(msg);
    setTimeout(() => location.reload(), 3000);
  } else {
    showPopup("Checkout Failed: " + (res.error || "Unknown"));
    btn.innerText = 'Pay Now';
    btn.disabled = false;
  }
}

/* UI HELPERS */
function toggleBasket() {
  const b = document.getElementById('basket');
  const container = document.querySelector('.order-container');
  b.classList.toggle('hidden');
  if (container) container.classList.toggle('basket-hidden');
}

let appliedVoucher = "";

function applyVoucherUI() {
  const code = document.getElementById('voucher-code').value.trim().toUpperCase();
  const msg = document.getElementById('voucher-msg');
  const select = document.getElementById('user-voucher-select');

  if (!code) {
    msg.innerText = "Please enter a code.";
    msg.style.color = "#ff4444";
    return;
  }

  // Sync select dropdown if code matches
  let found = false;
  if (select) {
    for (let opt of select.options) {
      if (opt.value === code) {
        select.value = code;
        found = true;
        break;
      }
    }
    if (!found) select.value = "";
  }

  if (code === 'FLAME-FIRST') {
    msg.innerText = "SUCCESS! RM 5.00 First-Time Discount Applied!";
    msg.style.color = "#00e676";
    appliedVoucher = code;
    updateCheckoutTotals(5.00);
  } else if (code === 'VOUCHER15') {
    // Check if we qualify visually
    const subText = document.getElementById('checkout-subtotal').innerText;
    const sub = parseFloat(subText.replace('RM ', ''));

    // We expect order.php to enforce the 5 item logic, but UI wise we just show it if valid code
    msg.innerText = "SURPRISE! 15% Discount Applied!";
    msg.style.color = "#00e676";
    appliedVoucher = code;
    updateCheckoutTotals(sub * 0.15); // Dynamic % discount
  } else if (code.startsWith('FLAME-')) {
    msg.innerText = "SUCCESS! RM 10.00 Voucher Applied!";
    msg.style.color = "#00e676";
    appliedVoucher = code;
    updateCheckoutTotals(10.00);
  } else {
    msg.innerText = "Invalid voucher code.";
    msg.style.color = "#ff4444";
  }
}

function applyVoucherDropdown() {
  const code = document.getElementById('user-voucher-select').value;
  const input = document.getElementById('voucher-code');
  if (!code) return;

  input.value = code;
  applyVoucherUI();
}

async function loadUserVouchers() {
  const select = document.getElementById('user-voucher-select');
  if (!select) return;

  const res = await api('action=get_vouchers');

  // Static options
  select.innerHTML = `
        <option value="">-- Choose a Voucher --</option>
    `;

  if (Array.isArray(res)) {
    res.forEach(v => {
      const opt = document.createElement('option');
      opt.value = v.code;
      opt.innerText = `${v.name} (${v.code})`;
      select.appendChild(opt);
    });
  }
}

function updateCheckoutTotals(discount) {
  const subText = document.getElementById('checkout-subtotal').innerText;
  const sub = parseFloat(subText.replace('RM ', ''));
  const discRow = document.getElementById('checkout-discount-row');
  const discVal = document.getElementById('checkout-discount-val');
  const totalEl = document.getElementById('checkout-total-amount');

  discRow.style.display = 'flex';
  discVal.innerText = `-RM ${discount.toFixed(2)}`;

  let final = sub - discount;
  if (final < 0) final = 0;

  totalEl.innerText = `RM ${final.toFixed(2)}`;
}

function openCheckout() {
  const listHtml = document.getElementById('basket-items').innerHTML;
  const items = document.querySelectorAll('.basket-item');
  if (items.length === 0) { showPopup("Basket is empty!"); return; }

  document.getElementById('checkout-items').innerHTML = listHtml;

  // Calculate Subtotal from basket
  let subtotal = 0;
  let totalQty = 0;
  items.forEach(item => {
    const pText = item.querySelector('p').innerText; // "RM 15.00 x 1"
    const price = parseFloat(pText.split('x')[0].replace('RM ', ''));
    const qty = parseInt(pText.split('x')[1]);
    subtotal += (price * qty);
    totalQty += qty;
  });

  const totalText = `RM ${subtotal.toFixed(2)}`;
  const subEl = document.getElementById('checkout-subtotal');
  const totalAmountEl = document.getElementById('checkout-total-amount');

  if (subEl) subEl.innerText = totalText;
  if (totalAmountEl) totalAmountEl.innerText = totalText;

  // Reset voucher UI
  appliedVoucher = "";
  const vCode = document.getElementById('voucher-code');
  const vMsg = document.getElementById('voucher-msg');
  const dRow = document.getElementById('checkout-discount-row');
  const vSelect = document.getElementById('user-voucher-select');

  if (vCode) vCode.value = "";
  if (vMsg) vMsg.innerText = "";
  if (dRow) dRow.style.display = 'none';
  if (vSelect) vSelect.value = "";

  // Auto-Apply Surprise Discount if qualified
  if (totalQty > 5) {
    if (vCode) vCode.value = "VOUCHER15";
    applyVoucherUI();
  }

  loadUserVouchers();

  loadUserVouchers();
  document.getElementById('checkout-modal').classList.add('show');
}

function closeCheckout() {
  document.getElementById('checkout-modal').classList.remove('show');
}

function renderBasket(items) {
  const list = document.getElementById('basket-items');
  const tot = document.getElementById('basket-total');
  document.querySelector('.count').innerText = items.length;

  let html = '';
  let total = 0;

  if (!items || items.length === 0) {
    list.innerHTML = "<div style='text-align:center;color:#777;padding:20px'>Basket is empty</div>";
    tot.innerText = "Total: RM 0.00";
    return;
  }

  const arr = Array.isArray(items) ? items : Object.values(items);
  let totalQty = 0;

  arr.forEach(i => {
    const q = parseInt(i.quantity || i.qty || 0);
    const p = parseFloat(i.price || 0);
    totalQty += q;

    html += `
        <div class="basket-item">
            <img src="${i.img}" onerror="this.src='assets/img/bimg1.png'">
            <div>
                <h4>${i.name}</h4>
                <p>RM ${p.toFixed(2)} x ${q}</p>
            </div>
            <button class="remove-btn" onclick="removeItem('${i.name}')"><i class="fas fa-trash"></i></button>
        </div>`;
    total += (p * q);
  });

  list.innerHTML = html;

  if (totalQty > 5) {
    showSurprisePopup();
    const discount = total * 0.15;
    const finalTotal = total - discount;
    tot.innerHTML = `
        <span class="strike-price">RM ${total.toFixed(2)}</span>
        <span class="discounted-price">RM ${finalTotal.toFixed(2)}</span>
        <span class="discount-badge">15% OFF</span>
    `;
  } else {
    tot.innerText = 'Total: RM ' + total.toFixed(2);
    surpriseShown = false; // Reset if they remove items to go below 5
  }
}

function showPopup(msg) {
  const p = document.getElementById('success-popup');
  p.innerText = msg;
  p.classList.add('show');
  setTimeout(() => {
    p.classList.remove('show');
  }, 3000);
}

function animateBasketBtn() {
  const b = document.getElementById('basket-toggle');
  b.style.transform = 'scale(1.2)';
  setTimeout(() => b.style.transform = 'scale(1)', 200);
}

function selectPayment(el) {
  document.querySelectorAll('.payment-option').forEach(e => e.classList.remove('selected'));
  el.classList.add('selected');
}

function processPayment() {
  confirmPay();
}
