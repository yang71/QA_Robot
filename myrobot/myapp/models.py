from django.db import models

# Create your models here.  User对应数据库中的表
class QA(models.Model):
    """
    创建qa表
    """
    question = models.CharField(max_length=500,  unique=True, primary_key=True, verbose_name='问题')
    answer = models.CharField(max_length=500,  verbose_name='答案')

    # meta类是配合admin进行管理的
    class Meta:
        db_table = 'qq_aa_y'
        verbose_name = '问答对'
        verbose_name_plural = verbose_name
