<?php
// This file contains just the schools list content for AJAX requests
// when navigating by letter
?>
    
<?php if ($result->num_rows > 0): ?>
    <div class="letter-section">
        <h2 class="letter-heading"><?php echo $activeLetter; ?></h2>
        <ul class="school-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="school-item">
                    <!-- Left side - School info -->
                    <div class="school-info">
                        <h2 class="school-title"><?php echo htmlspecialchars($row['name']); ?></h2>
                        <p class="school-address"><?php echo htmlspecialchars($row['address']); ?></p>
                        <?php if (!empty($row['email'])): ?>
                            <p class="school-email"><i class="fas fa-envelope email-icon"></i> <?php echo htmlspecialchars($row['email']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Right side - Grade offerings -->
                    <div class="school-grades">
                        <?php if ($row['offers_elementary'] == 1): ?>
                            <div class="grade-category">Elementary</div>
                            <div class="grade-levels"><?php echo htmlspecialchars($row['elementary_grades'] ?? 'Grades not specified'); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($row['offers_jhs'] == 1): ?>
                            <div class="grade-category">Junior High School</div>
                            <div class="grade-levels"><?php echo htmlspecialchars($row['jhs_grades'] ?? 'Grades not specified'); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($row['offers_shs'] == 1): ?>
                            <div class="grade-category">Senior High School</div>
                            <div class="grade-levels"><?php echo htmlspecialchars($row['shs_grades'] ?? 'Grades not specified'); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($row['offers_sped'] == 1): ?>
                            <div class="grade-category">SPED Program</div>
                            <div class="grade-levels">Special Education Program Available</div>
                        <?php endif; ?>
                        
                        <?php if (!$row['offers_elementary'] && !$row['offers_jhs'] && !$row['offers_shs'] && !$row['offers_sped']): ?>
                            <div class="grade-category">No grade information available</div>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
<?php else: ?>
    <div class="no-results">
        <h2>No schools found starting with letter '<?php echo $activeLetter; ?>'.</h2>
    </div>
<?php endif; ?>