from django.contrib import admin
from .models import (
    BusinessMetrics, HistoricalMetrics, ActivityFeed,
    ComplianceStatus, User, Organization
)

admin.site.site_header = "DeelFlow Administration"
admin.site.site_title = "DeelFlow Admin Portal"
admin.site.index_title = "Welcome to DeelFlow Dashboard"

@admin.register(BusinessMetrics)
class BusinessMetricsAdmin(admin.ModelAdmin):
    list_display = ("report_date", "total_revenue", "active_users", "total_deals")

@admin.register(HistoricalMetrics)
class HistoricalMetricsAdmin(admin.ModelAdmin):
    list_display = ("metric_type", "metric_value", "record_date")

@admin.register(ActivityFeed)
class ActivityFeedAdmin(admin.ModelAdmin):
    list_display = ("user_id", "action_type", "timestamp")

@admin.register(ComplianceStatus)
class ComplianceStatusAdmin(admin.ModelAdmin):
    list_display = ("compliance_percent", "system_health", "updated_at")
    
@admin.register(Organization)
class OrganizationAdmin(admin.ModelAdmin):
    list_display = ("id", "uuid", "name", "slug", "subscription_status", "created_at", "updated_at")
    search_fields = ("name", "slug", "uuid")
    list_filter = ("subscription_status", "created_at")


@admin.register(User)
class UserAdmin(admin.ModelAdmin):
    list_display = (
        "id", "uuid", "email", "first_name", "last_name", "phone",
        "role", "level", "points", "is_verified", "is_active",
        "organization", "created_at", "updated_at"
    )
    search_fields = ("email", "first_name", "last_name", "phone", "uuid")
    list_filter = ("role", "is_verified", "is_active", "organization", "created_at")
    ordering = ("-created_at",)
