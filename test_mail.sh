#!/bin/bash
cd "c:\Users\gesto\OneDrive\Desktop\ssg-management-system"
echo "Testing SMTP configuration with Artisan Tinker..."
echo ""
echo "use Illuminate\Support\Facades\\Mail;" > /tmp/test_mail.txt
echo "Mail::raw('Test Email from SSG System', function (\$message) { \$message->to('cpsuhinobaan.ssg.office@gmail.com')->subject('SMTP Test'); });" >> /tmp/test_mail.txt
echo ""
echo "Commands to run in tinker:"
cat /tmp/test_mail.txt
