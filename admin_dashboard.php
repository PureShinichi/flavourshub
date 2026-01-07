<?php
include "includes/header.php";

// Admin Check
if (!isset($user_data['role']) || $user_data['role'] != 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit();
}

// Handle Actions
$msg = "";
$status = "success";

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'update_status') {
        $oid = (int)$_POST['order_id'];
        $st = mysqli_real_escape_string($conn, $_POST['status']);
        if (mysqli_query($conn, "UPDATE cart_orders SET status='$st' WHERE order_id=$oid")) {
            $msg = "Order #$oid updated to $st";
        } else {
            $msg = "Error updating order";
            $status = "error";
        }
    }
    elseif ($_POST['action'] == 'update_role') {
        $uid = (int)$_POST['user_id'];
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        mysqli_query($conn, "UPDATE users SET role='$role' WHERE user_id=$uid");
        $msg = "User role updated.";
    }
    elseif ($_POST['action'] == 'adjust_points') {
        $uid = (int)$_POST['user_id'];
        $pts = (int)$_POST['points'];
        mysqli_query($conn, "UPDATE users SET points = points + ($pts) WHERE user_id=$uid");
        $msg = "Points adjusted for User #$uid";
    }
    elseif ($_POST['action'] == 'nuke_leaderboard') {
        mysqli_query($conn, "UPDATE users SET points = 0 WHERE role != 'admin'");
        $msg = "LEADERBOARD NUKED! All user points reset to 0.";
        $status = "warning";
    }
    elseif ($_POST['action'] == 'save_reward') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $cost = (int)$_POST['cost'];
        $cat = mysqli_real_escape_string($conn, $_POST['category']);
        $img = mysqli_real_escape_string($conn, $_POST['image_url']);
        
        if(isset($_POST['reward_id']) && !empty($_POST['reward_id'])) {
            $rid = (int)$_POST['reward_id'];
            mysqli_query($conn, "UPDATE reward_items SET name='$name', points_cost=$cost, category='$cat', image_url='$img' WHERE id=$rid");
            $msg = "Reward '$name' updated.";
        } else {
            mysqli_query($conn, "INSERT INTO reward_items (name, points_cost, category, image_url) VALUES ('$name', $cost, '$cat', '$img')");
            $msg = "New Reward '$name' added.";
        }
    }
    elseif ($_POST['action'] == 'save_menu') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $cat = mysqli_real_escape_string($conn, $_POST['category']);
        $price = (float)$_POST['price'];
        $pts = (int)$_POST['points'];
        $img = mysqli_real_escape_string($conn, $_POST['img']);
        
        if(isset($_POST['menu_id']) && !empty($_POST['menu_id'])) {
            $mid = (int)$_POST['menu_id'];
            mysqli_query($conn, "UPDATE menu_items SET name='$name', category='$cat', price=$price, points=$pts, img='$img' WHERE id=$mid");
            $msg = "Menu item '$name' updated.";
        } else {
            mysqli_query($conn, "INSERT INTO menu_items (name, category, price, points, img) VALUES ('$name', '$cat', $price, $pts, '$img')");
            $msg = "New Menu Item '$name' added.";
        }
    }
    elseif ($_POST['action'] == 'delete_menu') {
        $mid = (int)$_POST['menu_id'];
        mysqli_query($conn, "DELETE FROM menu_items WHERE id=$mid");
        $msg = "Menu item deleted.";
        $status = "warning";
    }
    elseif ($_POST['action'] == 'close_all_orders') {
        mysqli_query($conn, "UPDATE cart_orders SET status='completed' WHERE status='pending' OR status='confirmed'");
        $msg = "ALL ORDERS CLOSED & COMPLETED!";
        $status = "success";
    }
    elseif ($_POST['action'] == 'toggle_maintenance') {
        $val = $_POST['val'];
        mysqli_query($conn, "UPDATE settings SET val='$val' WHERE key_name='maintenance_mode'");
        $msg = "Maintenance mode " . ($val == '1' ? 'ENABLED' : 'DISABLED');
        $status = $val == '1' ? 'warning' : 'success';
    }
    elseif ($_POST['action'] == 'toggle_stock') {
        $mid = (int)$_POST['menu_id'];
        $val = (int)$_POST['available'];
        mysqli_query($conn, "UPDATE menu_items SET available=$val WHERE id=$mid");
        $msg = "Stock status updated.";
    }
}

// Fetch Maintenance Status
$maint_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT val FROM settings WHERE key_name='maintenance_mode'"));
$is_maintenance = ($maint_check['val'] ?? '0') == '1';

// Fetch Data
$stats = [
    'users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'],
    'orders' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM cart_orders"))['c'],
    'revenue' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(price*quantity) as c FROM cart_orders WHERE status IN ('confirmed','completed')"))['c'],
    'pending' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM cart_orders WHERE status='pending'"))['c']
];

$view = $_GET['view'] ?? 'dashboard';
?>

<div class="admin-container">
    <div class="fire-bg"></div>
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-branding">
            <div class="shield-icon"><i class="fas fa-shield-alt"></i></div>
            <span>OFFICIAL HUB</span>
            <small>Admin Command</small>
        </div>
        
        <nav class="sidebar-nav">
            <a href="?view=dashboard" class="<?= $view=='dashboard'?'active':'' ?>"><i class="fas fa-chart-pie"></i> Overview</a>
            <a href="?view=orders" class="<?= $view=='orders'?'active':'' ?>"><i class="fas fa-shopping-bag"></i> Orders</a>
            <a href="?view=users" class="<?= $view=='users'?'active':'' ?>"><i class="fas fa-user-shield"></i> Users</a>
            <a href="?view=rewards" class="<?= $view=='rewards'?'active':'' ?>"><i class="fas fa-gift"></i> Rewards</a>
            <a href="?view=menu" class="<?= $view=='menu'?'active':'' ?>"><i class="fas fa-utensils"></i> Menu Hub</a>
            <a href="?view=feedback" class="<?= $view=='feedback'?'active':'' ?>"><i class="fas fa-comment-alt"></i> Feedback</a>
            
            <div class="sidebar-status" style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.03);">
                <form method="POST">
                    <input type="hidden" name="action" value="toggle_maintenance">
                    <input type="hidden" name="val" value="<?= $is_maintenance ? '0' : '1' ?>">
                    <button type="submit" class="maint-btn <?= $is_maintenance ? 'active' : '' ?>">
                        <i class="fas fa-tools"></i> 
                        <?= $is_maintenance ? 'MAINTENANCE ON' : 'MAINTENANCE OFF' ?>
                    </button>
                </form>
            </div>

            <div class="sidebar-footer">
                <a href="dashboard.php" class="back-link"><i class="fas fa-external-link-alt"></i> Exit to Hub</a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <?php if($msg): ?>
            <div class="admin-alert <?= $status ?>">
                <i class="fas <?= $status == 'error' ? 'fa-exclamation-triangle' : ($status == 'warning' ? 'fa-radiation' : 'fa-check-circle') ?>"></i>
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <?php if($view == 'dashboard'): ?>
            <div class="page-header">
                <div class="header-main">
                    <h1>Hub Overview</h1>
                    <p>Monitoring the fire of FlavorHub.</p>
                </div>
            </div>

            <div class="admin-stat-grid">
                <div class="stat-card glass-card">
                    <div class="stat-icon rev"><i class="fas fa-coins"></i></div>
                    <div class="stat-info">
                        <h3>Total Revenue</h3>
                        <div class="value">RM <?= number_format($stats['revenue'], 2) ?></div>
                    </div>
                </div>
                <div class="stat-card glass-card">
                    <div class="stat-icon user"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3>Active Users</h3>
                        <div class="value"><?= $stats['users'] ?></div>
                    </div>
                </div>
                <div class="stat-card glass-card">
                    <div class="stat-icon ord"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <div class="value"><?= $stats['orders'] ?></div>
                    </div>
                </div>
                <div class="stat-card glass-card highlight">
                    <?php $f_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM contacts"))['c']; ?>
                    <div class="stat-icon pend"><i class="fas fa-star"></i></div>
                    <div class="stat-info">
                        <h3>Feedback Box</h3>
                        <div class="value"><?= $f_count ?></div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Snippet -->
            <div class="content-section">
                <div class="section-head">
                    <h2>Recent Orders</h2>
                    <a href="?view=orders" class="btn-sm">View All</a>
                </div>
                <?php 
                $rec = mysqli_query($conn, "SELECT * FROM cart_orders ORDER BY created_at DESC LIMIT 5");
                ?>
                <div class="table-wrapper">
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>Food</th><th>Status</th><th>Time</th></tr></thead>
                    <tbody>
                        <?php while($r=mysqli_fetch_assoc($rec)): ?>
                        <tr>
                            <td>#<?= $r['order_id'] ?></td>
                            <td><?= $r['food_name'] ?></td>
                            <td><span class="badge <?= $r['status'] ?>"><?= $r['status'] ?></span></td>
                            <td><?= date('H:i', strtotime($r['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            </div>

        <?php elseif($view == 'orders'): ?>
            <div class="page-header animate-slide-down">
                <div class="header-main">
                    <h1>Order Logistics</h1>
                    <p>Managing the Hub's delivery flame.</p>
                </div>
                <div class="danger-zone animate-up">
                    <div class="dz-info">
                        <h3><i class="fas fa-fire"></i> Order Management</h3>
                        <p>Complete all pending orders in one go.</p>
                    </div>
                    <form method="POST" onsubmit="return confirm('Close all pending orders?')">
                        <input type="hidden" name="action" value="close_all_orders">
                        <button type="submit" class="flaming-btn">
                            Close All Orders <i class="fas fa-fire"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="content-section">
                <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User ID</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $filter = $_GET['filter'] ?? '';
                        $sql = "SELECT * FROM cart_orders";
                        if($filter) $sql .= " WHERE status='$filter'";
                        $sql .= " ORDER BY created_at DESC";
                        $res = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($res)):
                            $total = $row['price'] * $row['quantity'];
                        ?>
                        <tr>
                            <td>#<?= $row['order_id'] ?></td>
                            <td>#<?= $row['user_id'] ?></td>
                            <td>
                                <div class="flex-align">
                                    <img src="<?= $row['img'] ?>" class="table-img">
                                    <div>
                                        <b><?= $row['food_name'] ?></b><br>
                                        <small>x<?= $row['quantity'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>RM <?= number_format($total, 2) ?></td>
                            <td><span class="badge <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                    <?php if($row['status'] == 'pending'): ?>
                                        <button type="submit" name="status" value="confirmed" class="btn-action confirm"><i class="fas fa-check"></i> Confirm</button>
                                        <button type="submit" name="status" value="cancelled" class="btn-action cancel"><i class="fas fa-times"></i> Cancel</button>
                                    <?php elseif($row['status'] == 'confirmed'): ?>
                                        <button type="submit" name="status" value="completed" class="btn-action complete"><i class="fas fa-archive"></i> Close Order</button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            </div>

        <?php elseif($view == 'users'): ?>
            <div class="page-header">
                <div class="header-main">
                    <h1>User Directory</h1>
                    <p>Managing the citizens of FlavourHub.</p>
                </div>
            </div>
            <div class="content-section">
                <div class="table-wrapper">
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>User</th><th>Points</th><th>Role</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php 
                        $res = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC");
                        while($u = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td>#<?= $u['user_id'] ?></td>
                            <td>
                                <b><?= $u['username'] ?></b><br>
                                <small><?= $u['email'] ?></small>
                            </td>
                            <td>
                                <form method="POST" class="flex-align">
                                    <input type="hidden" name="action" value="adjust_points">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <input type="number" name="points" value="0" style="width: 70px; background: rgba(0,0,0,0.3); border: 1px solid #333; color: white; padding: 5px; border-radius: 5px;">
                                    <button type="submit" class="btn-action confirm"><i class="fas fa-plus-minus"></i></button>
                                    <span style="font-weight: 800; margin-left:10px;"><?= $u['points'] ?></span>
                                </form>
                            </td>
                            <td><span class="badge <?= $u['role']=='admin'?'completed':'pending' ?>"><?= ucfirst($u['role']) ?></span></td>
                            <td>
                                <form method="POST" class="flex-align">
                                    <input type="hidden" name="action" value="update_role">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <select name="role" class="role-select">
                                        <option value="customer" <?= $u['role']=='customer'?'selected':'' ?>>Customer</option>
                                        <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                    </select>
                                    <button type="submit" class="btn-action confirm"><i class="fas fa-save"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            </div>

        <?php elseif($view == 'rewards'): ?>
            <div class="page-header">
                <div class="header-main">
                    <h1>Reward Hub Editor</h1>
                    <p>Manage the flame-shop inventory.</p>
                </div>
                <button onclick="toggleRewardForm()" class="btn-lava">Add New Reward</button>
            </div>

            <div id="reward-form" class="content-section" style="display:none; margin-bottom: 30px;">
                <form method="POST">
                    <input type="hidden" name="action" value="save_reward">
                    <input type="hidden" name="reward_id" id="edit_reward_id">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label>Reward Name</label>
                            <input type="text" name="name" id="edit_reward_name" required style="width:100%; padding:10px; background:#1a1a1a; border:1px solid #333; color:white;">
                        </div>
                        <div>
                            <label>Points Cost</label>
                            <input type="number" name="cost" id="edit_reward_cost" required style="width:100%; padding:10px; background:#1a1a1a; border:1px solid #333; color:white;">
                        </div>
                        <div>
                            <label>Category</label>
                            <select name="category" id="edit_reward_category" style="width:100%; padding:10px; background:#1a1a1a; border:1px solid #333; color:white;">
                                <option value="voucher">Voucher</option>
                                <option value="limited">Limited Edition</option>
                                <option value="merch">Merchandise</option>
                            </select>
                        </div>
                        <div>
                            <label>Image URL (e.g. assets/img/rewards/file.png)</label>
                            <input type="text" name="image_url" id="edit_reward_img" required style="width:100%; padding:10px; background:#1a1a1a; border:1px solid #333; color:white;">
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn-lava">Save Reward</button>
                        <button type="button" onclick="toggleRewardForm()" style="background:none; border:none; color:#888; cursor:pointer;">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="content-section">
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead><tr><th>Img</th><th>Name</th><th>Cost</th><th>Category</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php 
                            $rw_res = mysqli_query($conn, "SELECT * FROM reward_items");
                            while($rw = mysqli_fetch_assoc($rw_res)): ?>
                            <tr>
                                <td><img src="<?= $rw['image_url'] ?>" class="table-img"></td>
                                <td><b><?= $rw['name'] ?></b></td>
                                <td><?= $rw['points_cost'] ?> PTS</td>
                                <td><span class="badge blue"><?= $rw['category'] ?></span></td>
                                <td>
                                    <button onclick="editReward(<?= htmlspecialchars(json_encode($rw)) ?>)" class="btn-action confirm"><i class="fas fa-edit"></i> Edit</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
            function toggleRewardForm() {
                const f = document.getElementById('reward-form');
                f.style.display = f.style.display === 'none' ? 'block' : 'none';
                if(f.style.display === 'none') {
                    document.getElementById('edit_reward_id').value = '';
                    document.getElementById('edit_reward_name').value = '';
                    document.getElementById('edit_reward_cost').value = '';
                    document.getElementById('edit_reward_img').value = '';
                }
            }
            function editReward(data) {
                document.getElementById('reward-form').style.display = 'block';
                document.getElementById('edit_reward_id').value = data.id;
                document.getElementById('edit_reward_name').value = data.name;
                document.getElementById('edit_reward_cost').value = data.points_cost;
                document.getElementById('edit_reward_category').value = data.category;
                document.getElementById('edit_reward_img').value = data.image_url;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            </script>

        <?php elseif($view == 'menu'): ?>
            <div class="page-header animate-slide-down">
                <div class="header-main">
                    <h1>Menu Command Center</h1>
                    <p>Live control over your culinary arsenal.</p>
                </div>
                <button onclick="toggleMenuForm()" class="btn-brand">Add New Item <i class="fas fa-plus-circle"></i></button>
            </div>

            <div id="menu-form" class="content-section animate-up" style="display:none; margin-bottom: 30px; border-left: 5px solid var(--primary);">
                <form method="POST">
                    <input type="hidden" name="action" value="save_menu">
                    <input type="hidden" name="menu_id" id="edit_menu_id">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div class="form-group">
                            <label>Item Name</label>
                            <input type="text" name="name" id="edit_menu_name" required style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid #333; color:white; border-radius:10px;">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" id="edit_menu_cat" style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid #333; color:white; border-radius:10px;">
                                <option value="burgers">Burgers</option>
                                <option value="pizzas">Pizzas</option>
                                <option value="pastas">Pastas</option>
                                <option value="coffee">Coffee</option>
                                <option value="frappe">Frappe</option>
                                <option value="fruity">Fruity</option>
                                <option value="ice_cream">Ice Cream</option>
                                <option value="cakes">Cakes</option>
                                <option value="bingsu">Bingsu</option>
                                <option value="pastries">Pastries</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price (RM)</label>
                            <input type="number" step="0.01" name="price" id="edit_menu_price" required style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid #333; color:white; border-radius:10px;">
                        </div>
                        <div class="form-group">
                            <label>Sparks Gained</label>
                            <input type="number" name="points" id="edit_menu_points" required style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid #333; color:white; border-radius:10px;">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Image Source Path</label>
                            <input type="text" name="img" id="edit_menu_img" required placeholder="assets/img/bimg1.png" style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid #333; color:white; border-radius:10px;">
                        </div>
                    </div>
                    <div style="margin-top: 30px; display: flex; gap: 15px;">
                        <button type="submit" class="btn-brand">Save Changes <i class="fas fa-save"></i></button>
                        <button type="button" onclick="toggleMenuForm()" class="filter-btn">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="content-section animate-up">
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead><tr><th>Visual</th><th>Item Name</th><th>Price</th><th>Category</th><th>Inventory Action</th></tr></thead>
                        <tbody>
                            <?php 
                            $m_res = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY category, name");
                            while($m = mysqli_fetch_assoc($m_res)): ?>
                            <tr class="fade-in">
                                <td><img src="<?= $m['img'] ?>" class="table-img" style="width:60px; height:60px; border-radius:12px; border: 1px solid rgba(255,255,255,0.1);"></td>
                                <td>
                                    <strong style="font-size:1.1rem;"><?= $m['name'] ?></strong><br>
                                    <small style="color:var(--primary);">+<?= $m['points'] ?> Sparks</small>
                                </td>
                                <td><span style="font-weight:900;">RM <?= number_format($m['price'], 2) ?></span></td>
                                <td><span class="badge" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);"><?= strtoupper($m['category']) ?></span></td>
                                <td>
                                    <div style="display:flex; gap:10px; align-items:center;">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="toggle_stock">
                                            <input type="hidden" name="menu_id" value="<?= $m['id'] ?>">
                                            <input type="hidden" name="available" value="<?= $m['available'] ? '0' : '1' ?>">
                                            <button type="submit" class="btn-stock <?= $m['available'] ? 'instock' : 'outstock' ?>">
                                                <?= $m['available'] ? '<i class="fas fa-check"></i> IN STOCK' : '<i class="fas fa-times"></i> OUT OF STOCK' ?>
                                            </button>
                                        </form>
                                        <button onclick="editMenu(<?= htmlspecialchars(json_encode($m)) ?>)" class="btn-action confirm"><i class="fas fa-edit"></i></button>
                                        <form method="POST" onsubmit="return confirm('Archive this item?')">
                                            <input type="hidden" name="action" value="delete_menu">
                                            <input type="hidden" name="menu_id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn-action cancel"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
            function toggleMenuForm() {
                const f = document.getElementById('menu-form');
                f.style.display = f.style.display === 'none' ? 'block' : 'none';
                if(f.style.display === 'none') {
                    document.getElementById('edit_menu_id').value = '';
                    document.getElementById('edit_menu_name').value = '';
                }
            }
            function editMenu(data) {
                document.getElementById('menu-form').style.display = 'block';
                document.getElementById('edit_menu_id').value = data.id;
                document.getElementById('edit_menu_name').value = data.name;
                document.getElementById('edit_menu_cat').value = data.category;
                document.getElementById('edit_menu_price').value = data.price;
                document.getElementById('edit_menu_points').value = data.points;
                document.getElementById('edit_menu_img').value = data.img;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            </script>

        <?php elseif($view == 'feedback'): ?>
            <div class="page-header">
                <div class="header-main">
                    <h1>Customer Voice</h1>
                    <p>Ratings and feedback from the Hub's population.</p>
                </div>
            </div>
            <div class="content-section">
                <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>Rating</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res = mysqli_query($conn, "SELECT * FROM contacts ORDER BY created_at DESC");
                        while($f = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td>
                                <b><?= $f['name'] ?></b><br>
                                <small style="color: #666;"><?= $f['email'] ?></small>
                            </td>
                            <td>
                                <div style="color: #ffc107;">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="<?= $i <= $f['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td style="max-width: 300px; white-space: normal; line-height: 1.4;"><?= $f['message'] ?></td>
                            <td><?= date('M d, Y', strtotime($f['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($res) == 0): ?>
                            <tr><td colspan="4" style="text-align: center; color: #555; padding: 50px;">No feedback received yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
/* Hide Global Header for immersive Admin experience */
header { display: none !important; }

/* Improve Global Admin Smoothness */
* { transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease, transform 0.2s ease; }
.admin-sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

.admin-alert {
    padding: 20px 30px; border-radius: 15px; margin-bottom: 30px; font-weight: 700;
    display: flex; align-items: center; gap: 15px;
    animation: slide-in 0.5s ease;
}
.admin-alert.success { background: rgba(0, 230, 118, 0.1); color: #00e676; border: 1px solid rgba(0, 230, 118, 0.2); }
.admin-alert.error { background: rgba(255, 68, 68, 0.1); color: #ff4444; border: 1px solid rgba(255, 68, 68, 0.2); }
.admin-alert.warning { background: rgba(255, 157, 0, 0.1); color: #ff9d00; border: 1px solid rgba(255, 157, 0, 0.2); animation: shake 0.5s; }

@keyframes slide-in { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-10px); } 75% { transform: translateX(10px); } }

.animate-up { animation: animate-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
.animate-slide-down { animation: animate-slide-down 0.8s cubic-bezier(0.16, 1, 0.3, 1) both; }
.fade-in { animation: fade-in 0.5s ease out both; }

@keyframes animate-up {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
@keyframes animate-slide-down {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Glassy hover effect for table rows */
.admin-table tr { transition: 0.3s; }
.admin-table tr:hover { 
    background: rgba(255, 78, 0, 0.05) !important; 
    box-shadow: inset 5px 0 0 var(--primary);
}

/* --- PERFECT ADMIN CSS --- */
.admin-container {
    display: flex;
    min-height: 100vh; /* Full height */
    background: #050505;
}

/* Sidebar Styling */
.admin_dashboard-wrapper { display: none; } /* Hide old wrapper if exists */

.admin-sidebar {
    width: 280px; /* Slightly slimmer */
    background: #0a0a0a;
    border-right: 1px solid rgba(255, 78, 0, 0.08);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
    z-index: 1000;
}

.sidebar-branding {
    padding: 50px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    margin-bottom: 20px;
}

.shield-icon {
    font-size: 2.2rem;
    color: var(--primary);
    margin-bottom: 12px;
    filter: drop-shadow(0 0 15px rgba(255, 78, 0, 0.4));
    animation: pulse-shield 3s infinite ease-in-out;
}
@keyframes pulse-shield {
    0%, 100% { transform: scale(1); opacity: 0.8; filter: drop-shadow(0 0 10px rgba(255, 78, 0, 0.2)); }
    50% { transform: scale(1.05); opacity: 1; filter: drop-shadow(0 0 20px rgba(255, 78, 0, 0.5)); }
}

.sidebar-branding span {
    display: block;
    font-size: 1rem;
    font-weight: 900;
    color: white;
    letter-spacing: 3px;
}
.sidebar-branding small {
    color: #444;
    text-transform: uppercase;
    font-size: 0.6rem;
    letter-spacing: 2px;
    margin-top: 5px;
    display: block;
}

.sidebar-nav {
    flex: 1;
    padding: 0 15px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    color: #555;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    font-weight: 700;
    font-size: 0.9rem;
    border: 1px solid transparent;
}

.sidebar-nav a i { font-size: 1.1rem; width: 25px; text-align: center; }

.sidebar-nav a:hover, .sidebar-nav a.active {
    background: rgba(255, 78, 0, 0.08);
    color: var(--primary);
    border-color: rgba(255, 78, 0, 0.1);
    transform: translateX(5px);
}
.sidebar-nav a.active {
    background: linear-gradient(90deg, rgba(255, 78, 0, 0.15), transparent);
    box-shadow: -3px 0 0 var(--primary);
}

.sidebar-footer {
    margin-top: auto;
    padding: 20px 15px;
    border-top: 1px solid rgba(255,255,255,0.03);
}
.back-link {
    justify-content: center;
    color: #777 !important;
}
.back-link:hover {
    color: white !important;
    background: rgba(255,255,255,0.05) !important;
}

/* Main Content Styling */
.admin-content {
    flex: 1;
    padding: 60px 80px;
    background: 
        radial-gradient(circle at 100% 0%, rgba(255, 78, 0, 0.04) 0%, transparent 40%),
        radial-gradient(circle at 0% 100%, rgba(255, 78, 0, 0.02) 0%, transparent 40%);
    min-width: 0;
    overflow-y: auto;
    height: 100vh;
}

.page-header {
    margin-bottom: 60px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.page-header h1 { 
    font-size: 3.5rem; 
    font-weight: 950; 
    letter-spacing: -2px;
    background: linear-gradient(180deg, #fff 0%, #777 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.page-header p { color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; }

/* Stats Grid */
.admin-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
    margin-bottom: 60px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 35px;
    border-radius: 28px;
    transition: 0.4s;
    border: 1px solid rgba(255,255,255,0.05);
}
.stat-card:hover { transform: translateY(-10px) scale(1.02); border-color: rgba(255, 78, 0, 0.2); }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.stat-icon.rev { background: rgba(0, 230, 118, 0.1); color: #00e676; }
.stat-icon.user { background: rgba(0, 176, 255, 0.1); color: #00b0ff; }
.stat-icon.ord { background: rgba(255, 23, 68, 0.1); color: #ff124d; }
.stat-icon.pend { background: rgba(255, 157, 0, 0.1); color: #ff9d00; }

.stat-info h3 { font-size: 0.75rem; color: #555; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; margin-bottom: 5px; }
.stat-info .value { font-size: 1.8rem; font-weight: 900; color: white; }

/* Tables */
.content-section {
    background: rgba(255,255,255,0.02);
    border-radius: 35px;
    padding: 50px;
    border: 1px solid rgba(255,255,255,0.03);
}
.section-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
.section-head h2 { font-size: 1.8rem; font-weight: 850; }

.admin-table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
.admin-table th { padding: 0 30px 15px; color: #444; font-size: 0.7rem; text-transform: uppercase; font-weight: 900; letter-spacing: 2px; text-align: left; }
.admin-table td { padding: 30px; background: rgba(255,255,255,0.01); transition: 0.3s; }
.admin-table td:first-child { border-radius: 20px 0 0 20px; border-left: 1px solid rgba(255,255,255,0.02); }
.admin-table td:last-child { border-radius: 0 20px 20px 0; border-right: 1px solid rgba(255,255,255,0.02); }
.admin-table tr:hover td { background: rgba(255, 78, 0, 0.05); border-color: rgba(255, 78, 0, 0.1); transform: scale(1.005); }

.table-img { width: 50px; height: 50px; border-radius: 12px; object-fit: cover; }
.badge { padding: 8px 16px; border-radius: 25px; font-size: 0.65rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }

/* Buttons & Forms */
.btn-brand {
    background: var(--primary);
    color: white;
    padding: 15px 35px;
    border-radius: 18px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.btn-brand:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(255, 78, 0, 0.3); }

.role-select {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
    padding: 10px 15px;
    border-radius: 12px;
    margin-right: 15px;
}

.modal-content {
    border-radius: 40px;
    padding: 60px;
    background: rgba(10,10,10,0.9);
}

.filters { display: flex; gap: 15px; }
.filter-btn {
    padding: 12px 25px;
    border-radius: 15px;
    background: rgba(255,255,255,0.03);
    color: #666;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    transition: 0.3s;
}
.filter-btn.active, .filter-btn:hover { background: rgba(255, 78, 0, 0.1); color: var(--primary); }

/* Advanced Admin Styles */
.danger-zone {
    background: rgba(255, 0, 0, 0.05);
    border: 1px dashed rgba(255, 0, 0, 0.3);
    padding: 25px;
    border-radius: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}
.dz-info h3 { color: #ff4444; font-size: 1.1rem; margin-bottom: 5px; }
.dz-info p { font-size: 0.8rem; color: #888; margin: 0; }

.flaming-btn {
    background: linear-gradient(45deg, #ff4e00, #ff8c00, #ff0000);
    background-size: 200% 200%;
    animation: flaming-burn 3s ease infinite;
    box-shadow: 0 5px 15px rgba(255, 78, 0, 0.3);
    border: none;
    color: white;
    padding: 15px 30px;
    border-radius: 12px;
    font-weight: 900;
    cursor: pointer;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: 0.3s;
}

.flaming-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 78, 0, 0.5);
}

@keyframes flaming-burn {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.maint-btn {
    width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #333;
    background: rgba(255,255,255,0.05); color: #888; font-weight: 800; cursor: pointer;
    transition: 0.3s;
}
.maint-btn.active {
    background: rgba(255, 157, 0, 0.1); color: #ff9d00; border-color: #ff9d00;
    box-shadow: 0 0 15px rgba(255, 157, 0, 0.2);
}

.btn-stock {
    padding: 8px 12px; border-radius: 8px; font-weight: 900; font-size: 0.7rem; border: none; cursor: pointer;
}
.btn-stock.instock { background: rgba(0, 230, 118, 0.1); color: #00e676; }
.btn-stock.outstock { background: rgba(255, 68, 68, 0.1); color: #ff4444; }

.animate-up { animation: animate-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
.animate-slide-down { animation: animate-slide-down 0.8s cubic-bezier(0.16, 1, 0.3, 1) both; }
.fade-in { animation: fade-in 0.5s ease both; }

@keyframes animate-up {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
@keyframes animate-slide-down {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
<?php include "includes/footer.php"; ?>
