# simple-cntrol-panel-for-moving-robot
A web-based control panel for a robot, combining manual button controls and voice command recognition, with all activity logged to a MySQL database. Built with plain HTML/JS on the frontend and PHP on the backend, hosted on InfinityFree.
---

## 1. Project Overview

This project has three parts:

1. **Manual Control Panel** — a directional pad (forward, backward, left, right, stop) that sends movement commands to a database. A physical robot (e.g. an Arduino/ESP32-based device) polls this database and executes the latest command.
2. **Voice-to-Text Interface** — uses the browser's built-in Web Speech API to convert spoken words into text, live, with no external API or key required.
3. **Voice Command Execution** — recognized speech is matched against a set of keywords (forward/backward/left/right/stop, in Arabic and English). If a match is found, the same command is sent as if a button were pressed. 

Everything lives on a single page (`index.html`): the control pad on top, the voice section below it.

---

## 2. Architecture

```
Browser (index.html)
   |
   |-- Button click ----------> update_command.php --> robot_state table (single row, id=1)
   |
   |-- Speech recognized ----> save_voice.php -------> voice_texts table (append-only log)
   |                     \
   |                      \--> (if keyword matched) --> update_command.php --> robot_state table
   |

Physical robot (separate device, not included in this repo)
   |
   |-- Polls -----------------> get_state.php --------> reads robot_state table
```

One MySQL database is used for everything — it just contains two separate tables:

- `robot_state` — one row (id = 1) that's continuously overwritten with the latest movement command.
- `voice_texts` — an append-only log of every recognized voice phrase, with a timestamp.

---

## 3. File Structure

All files sit in the same folder inside `htdocs` on the hosting account.

| File | Purpose |
|---|---|
| `index.html` | The single-page UI: control pad + voice section |
| `db.php` | Database connection config (host, user, password, db name) |
| `update_command.php` | Receives a command (from a button click or a matched voice command) and writes it to `robot_state` |
| `get_state.php` | Returns the current robot command as JSON (used by the physical robot to poll) |
| `save_voice.php` | Receives recognized speech text and inserts it into `voice_texts` |
| `setup.sql` | One-time SQL script to create the `robot_state` table and seed it |
| `voice_setup.sql` | One-time SQL script to create the `voice_texts` table |

---

## 4. Database Schema

### `robot_state`
Holds exactly one row that represents the robot's current command. It's updated in place, never appended to.

```sql
CREATE TABLE robot_state (
    id INT PRIMARY KEY,
    command CHAR(1) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO robot_state (id, command) VALUES (1, 'S');
```

Command letters stored:
| Button | Letter stored |
|---|---|
| forward | f |
| backward | b |
| left | l |
| right | r |
| stop | S |

### `voice_texts`
An append-only history of every phrase recognized by the voice module.

```sql
CREATE TABLE voice_texts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5. Setup Instructions

### Step 1 — Create the database
1. In the InfinityFree control panel (vPanel), go to **MySQL Databases**.
2. Create a new database. InfinityFree will automatically prefix the name you choose, e.g. `if0_XXXXXXXX_yourname`.
3. Note down the four connection values shown: **Hostname**, **Username**, **Database name**, and the **password** you set.

### Step 2 — Create the tables
1. Open **phpMyAdmin** from the vPanel next to your database.
2. Go to the **SQL** tab.
3. Paste and run the contents of `setup.sql`.
4. Paste and run the contents of `voice_setup.sql`.
5. Confirm both `robot_state` and `voice_texts` now appear in the table list.

### Step 3 — Configure the connection
Open `db.php` and fill in the exact values copied in Step 1:

```php
$host   = "sqlXXX.infinityfree.com";
$user   = "if0_XXXXXXXX";
$pass   = "your_actual_password";
$dbname = "if0_XXXXXXXX_yourname";
```

### Step 4 — Upload the files
Upload all files (`index.html`, `db.php`, `update_command.php`, `get_state.php`, `save_voice.php`) to the same folder inside `htdocs` (e.g. `htdocs/panel/`).

### Step 5 — Test
1. Open `get_state.php` directly in the browser (e.g. `https://yoursite.com/panel/get_state.php`). It should return JSON like:
   ```json
   {"command":"S","updated_at":"2026-08-17 15:46:05"}
   ```
2. Open `index.html` (or just the folder URL, since `index.html` loads by default) and test the buttons and the microphone.

---

## 6. How Voice Control Works

1. The user picks a language (Arabic or English) and taps the microphone button.
2. The Web Speech API (`SpeechRecognition` / `webkitSpeechRecognition`) transcribes speech live in the browser — no server or external API involved in the transcription itself.
3. Once a **final** result is produced:
   - The full text is sent to `save_voice.php` and stored in `voice_texts` (this always happens, regardless of whether it matches a command).
   - The text is normalized (Arabic letter variants like أ/إ/آ/ا are unified, diacritics stripped) and checked against a keyword list.
   - If a keyword matches, the corresponding command is sent to `update_command.php` — exactly as if the matching button had been clicked.

### Recognized keywords

| Command | Arabic keywords | English keywords |
|---|---|---|
| forward | قدام، للأمام، أمام، روح | forward, go |
| backward | خلف، للخلف، ورا، رجوع | backward, back |
| left | يسار، شمال | left |
| right | يمين | right |
| stop | قف، وقف، توقف، استوب | stop |

Keyword matching is a simple substring check, so a full sentence like "روح قدام شوي" will still match "forward" because it contains "قدام".

---

## 7. Notes & Limitations

- **HTTPS required.** The Web Speech API only works over HTTPS (or `localhost`). Make sure the InfinityFree domain used has SSL enabled.
- **Must be accessed via URL, not opened as a local file.** Opening `index.html` directly from disk (`file://...`) will break the fetch calls to the PHP endpoints — it must be loaded through the actual hosted URL.
- **The physical robot side is not included here.** This project only covers the web control panel and database logging; the robot itself is expected to poll `get_state.php` periodically and act on the returned command.

---
