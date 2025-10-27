#!/bin/bash

# === CONFIGURATION ===
DB_NAME="your_database_name"
DB_USER="your_username"
DB_PASS="your_password"
BACKUP_DIR="/var/backups/mariadb"
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

# === KEEP ONLY TWO MOST RECENT BACKUPS LOCALLY ===
echo "[INFO] Cleaning up old backups..."
cd "$BACKUP_DIR" || exit
ls -t ${DB_NAME}_*.zip | tail -n +3 | xargs -r rm --

# === UPLOAD TO AWS S3 ===
echo "[INFO] Uploading to S3..."
aws s3 cp "$ZIP_FILE" "$S3_BUCKET/" --region "$AWS_REGION" --profile "$AWS_PROFILE"

if [ $? -eq 0 ]; then
  echo "[SUCCESS] Backup uploaded to $S3_BUCKET"
else
  echo "[ERROR] Failed to upload to S3"
fi

# === LOG SUCCESS ===
echo "[DONE] Backup process completed: $ZIP_FILE"