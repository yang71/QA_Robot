from django.contrib import admin
from myapp.models import *
from django.db.models import Q

admin.site.site_title = "后台管理系统"
admin.site.site_header = "QA_robot"

class QAAdmin(admin.ModelAdmin):
    def get_queryset(self, request):
        qs = super(QAAdmin, self).get_queryset(request)
        if request.user.is_superuser:
            return qs
        else:
            return qs.filter(author=request.user)

    list_per_page = 20
    search_fields = ('question',)
    ordering = ('question',)
    def changelist_view(self, request, extra_context=None):
        user = request.user
        if user.is_superuser:
            self.list_display = ['question', 'answer']
            self.list_filter = ('question', )
        else:
            self.list_display = ['question', 'answer']
            self.list_editable = ['question', 'answer']
            self.list_filter=()
            self.list_display_links = [""]
        return super(QAAdmin, self).changelist_view(request, extra_context=None)


admin.site.register(QA,QAAdmin)