#!/bin/bash

# === CONFIGURATION ===
DB_NAME="your_database_name"
DB_USER="your_username"
DB_PASS="your_password"
BACKUP_DIR="./db_backups"
ZIP_PASSWORD="YourStrongPassword"


# === AWS CONFIG ===
S3_BUCKET="s3://your-bucket-name/mariadb-backups"
AWS_PROFILE="default"   # or specify a named profile
AWS_REGION="us-east-1"

# === CREATE BACKUP DIRECTORY IF NOT EXISTS ===
mkdir -p "$BACKUP_DIR"

# === FILE NAMES ===
DATE=$(date +"%Y-%m-%d_%H-%M-%S")
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
zip --password "$ZIP_PASSWORD" --encrypt "$ZIP_FILE" "$SQL_FILE"

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


# sudo chmod +x /usr/local/bin/database_backup_script.sh

# crontab -e

# 0 0 * * * /usr/local/bin/db_backup.sh >> /var/log/db_backup.log 2>&1

# unzip -P "YourStrongPassword" your_backup.zip