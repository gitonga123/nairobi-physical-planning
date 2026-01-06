#!/bin/bash

# === CONFIGURATION ===
DB_NAME="uasin_gishu_prod"
DB_USER="root"
DB_PASS="R1#venue@2025!"
BACKUP_DIR="/var/www/revenue/uasingishu_webapp/webapp/db_backups"
ZIP_PASSWORD="O7v£77"

# === CREATE BACKUP DIRECTORY IF NOT EXISTS ===
mkdir -p "$BACKUP_DIR"

# === FILE NAMES ===
DATE=$(date +"%Y-%m-%d")
SQL_FILE="$BACKUP_DIR/${DB_NAME}_$DATE.sql"
ZIP_FILE="$BACKUP_DIR/${DB_NAME}_$DATE.zip"

# === DUMP DATABASE ===
echo "[INFO] Dumping database..."
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$SQL_FILE"

if [ $? -ne 0 ]; then
  echo "[ERROR] Database dump failed!"
  rm -f "$SQL_FILE"
  exit 1
fi

# === ZIP WITH PASSWORD ===
echo "[INFO] Zipping backup..."
zip -P "$ZIP_PASSWORD" "$ZIP_FILE" "$SQL_FILE"

# === DELETE UNZIPPED SQL FILE ===
echo "[INFO] Deleting raw SQL file..."
rm -f "$SQL_FILE"

# === KEEP ONLY TWO MOST RECENT BACKUPS ===
echo "[INFO] Cleaning up old backups..."
cd "$BACKUP_DIR" || exit
# List files sorted by time, skip the newest two, and delete the rest
ls -t ${DB_NAME}_*.zip | tail -n +3 | xargs -r rm --

# === LOG SUCCESS ===
echo "[SUCCESS] Backup completed: $ZIP_FILE"


# sudo chmod +x /var/www/revenue/uasingishu_webapp/webapp/usn_database_backup_script.sh

# crontab -e

# 0 */6 * * * /var/www/revenue/uasingishu_webapp/webapp/usn_database_backup_script.sh >> /var/www/revenue/uasingishu_webapp/webapp/bk_logs/db_backup.log 2>&1

# unzip -P "YourStrongPassword" your_backup.zip