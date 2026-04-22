<?php
/**
 * Recent Activity Widget
 * 
 * Usage:  <div data-component="recent-activity"></div>
 * 
 * Displays a list of recent system activities.
 * In production, this would fetch from the database.
 */
?>

<div class="recent-activity">
  <div class="widget-header">
    <h3>Recent Activity</h3>
    <a href="#" class="widget-link">View All</a>
  </div>
  <ul class="activity-list" id="activity-list">
    <li class="activity-item">
      <span class="activity-icon"></span>
      <div class="activity-details">
        <p class="activity-text">New student registered</p>
        <span class="activity-time">2 minutes ago</span>
      </div>
    </li>
    <li class="activity-item">
      <span class="activity-icon"></span>
      <div class="activity-details">
        <p class="activity-text">Queue #45 completed</p>
        <span class="activity-time">10 minutes ago</span>
      </div>
    </li>
    <li class="activity-item">
      <span class="activity-icon"></span>
      <div class="activity-details">
        <p class="activity-text">System settings updated</p>
        <span class="activity-time">1 hour ago</span>
      </div>
    </li>
  </ul>
</div>