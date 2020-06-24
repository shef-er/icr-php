CREATE EXTENSION "uuid-ossp";

CREATE TYPE "presence_type" AS ENUM (
  'Я',
  'Вых',
  'ОС',
  'Отг',
  'УО',
  'П',
  'Б',
  'О'
);

CREATE TYPE "event_type" AS ENUM (
  'create',
  'update',
  'delete'
);

CREATE TABLE "events" (
  "event_id" SERIAL PRIMARY KEY,
  "entity_id" uuid,
  "type" event_type,
  "body" jsonb,
  "version" int,
  "timestamp" timestamp,
  "user_id" uuid
);

CREATE TABLE "api_tokens" (
  "token_id" SERIAL PRIMARY KEY,
  "user_id" uuid,
  "expiry" timestamp
);

CREATE TABLE "actors" (
  "id" uuid PRIMARY KEY,
  "full_name" varchar,
  "role" varchar
);

CREATE TABLE "users" (
  "id" uuid PRIMARY KEY,
  "registration_date" date,
  "credentials" jsonb
);