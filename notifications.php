<?php
     $titre_page = "Notifications";
     require_once 'includes/header.php';
     if (!estConnecte()) {
         rediriger('register.php');
     }
     $database = new Database();
     $db = $database->connect();
     $query = "SELECT id, type, message, related_id, is_read, created_at
               FROM notifications
               WHERE utilisateur_id = :utilisateur_id
               ORDER BY created_at DESC";
     $stmt = $db->prepare($query);
     $stmt->bindParam(':utilisateur_id', $_SESSION['utilisateur_id'], PDO::PARAM_INT);
     $stmt->execute();
     $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
     ?>
     <section class="notifications-section">
         <div class="container">
             <h1>Notifications</h1>
             <div class="notifications-list">
                 <?php if (empty($notifications)): ?>
                     <p class="no-notifications">Aucune notification.</p>
                 <?php else: ?>
                     <?php foreach ($notifications as $notif): ?>
                         <div class="notification-item <?php echo $notif['is_read'] ? 'read' : ''; ?>">
                             <p><?php echo htmlspecialchars($notif['message']); ?></p>
                             <span class="time"><?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?></span>
                             <?php if ($notif['type'] === 'new_quiz' && $notif['related_id']): ?>
                                 <a href="quiz.php?id=<?php echo $notif['related_id']; ?>" class="view-notif">Voir</a>
                             <?php elseif ($notif['type'] === 'high_score' && $notif['related_id']): ?>
                                 <a href="profil.php#history" class="view-notif">Voir</a>
                             <?php endif; ?>
                         </div>
                     <?php endforeach; ?>
                 <?php endif; ?>
             </div>
         </div>
     </section>
     <style>
     .notifications-section {
         padding: 40px 0;
     }
     .notifications-list {
         max-width: 600px;
         margin: 0 auto;
     }
     .notification-item {
         background: white;
         padding: 15px;
         border-radius: 8px;
         margin-bottom: 10px;
         box-shadow: 0 2px 4px rgba(0,0,0,0.1);
     }
     .notification-item.read {
         background: #f8fafc;
     }
     .notification-item p {
         margin: 0 0 5px;
     }
     .notification-item .time {
         font-size: 12px;
         color: #777;
     }
     .view-notif {
         color: #2563eb;
         text-decoration: none;
         font-size: 14px;
     }
     .view-notif:hover {
         text-decoration: underline;
     }
     .no-notifications {
         text-align: center;
         color: #777;
     }
     </style>
     <?php require_once 'includes/footer.php'; ?>
