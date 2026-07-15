# 🎬 Cinema Ticket System — JavaFX

A full-featured cinema management system built with JavaFX.

---

## 📁 Project Structure

```
CinemaTicketSystem/
├── src/
│   ├── Main.java               ← App entry point
│   ├── LoginScreen.java        ← Login & Registration UI
│   ├── AdminDashboard.java     ← Admin panel (movies, bookings, users, revenue)
│   ├── CustomerMenu.java       ← Customer browsing & booking
│   ├── SeatSelectionScreen.java← Interactive 10×10 seat picker
│   ├── MovieEditor.java        ← Add/Edit movie dialog
│   ├── Movie.java              ← Movie data model
│   ├── Booking.java            ← Booking data model
│   ├── MovieManager.java       ← Movie file I/O
│   ├── BookingManager.java     ← Booking file I/O
│   ├── SeatManager.java        ← Seat grid management per movie
│   ├── PriceCalculator.java    ← Ticket + snack pricing
│   └── UserDatabase.java       ← User auth & file I/O
├── run.bat                     ← Windows run script
├── run.sh                      ← Linux/Mac run script
└── README.md
```

---

## 🚀 Setup & Run

### Prerequisites
- **Java 11+** (JDK)
- **JavaFX SDK** — Download from https://openjfx.io/

### Steps

1. Download **JavaFX SDK** and extract it (e.g. to `C:\javafx-sdk-21\`)

2. Open the project folder in your terminal / command prompt

3. **Compile** (replace the path with your JavaFX SDK location):
```bat
javac --module-path "C:\javafx-sdk-21\lib" --add-modules javafx.controls,javafx.fxml -d bin src\*.java
```

4. **Run**:
```bat
java --module-path "C:\javafx-sdk-21\lib" --add-modules javafx.controls,javafx.fxml -cp bin Main
```

> 💡 **VS Code Users**: Install the "Extension Pack for Java" and "JavaFX Support" extensions. Add the JavaFX lib to your `.vscode/settings.json`.

---

## 🔑 Default Login Credentials

| Username | Password  | Role     |
|----------|-----------|----------|
| admin    | admin123  | Admin    |
| john     | john123   | Customer |
| alice    | alice123  | Customer |

---

## ✨ Features

### 👤 Customer
- Browse all movies (title, genre, duration, rating, base price)
- Interactive **10×10 seat map** with colour-coded seat types
  - 🟥 **VIP** (rows A–C) — 1.5× price
  - 🟦 **Standard** (rows D–G) — base price
  - ⬛ **Economy** (rows H–J) — 0.75× price
- Select **snacks** (Popcorn, Drinks, Nachos, Combo…)
- Real-time **price breakdown** before confirming
- View & **cancel** past bookings

### 🔒 Admin
- **Dashboard** — overview stats (total bookings, revenue, movies, users)
- **Movie Manager** — add, edit, delete movies with all details
- **All Bookings** — full booking table with filters
- **User Manager** — view and delete users
- **Revenue Report** — total revenue and per-movie booking counts

---

## 💾 Data Files (auto-created on first run)

| File | Contents |
|------|----------|
| `users.txt` | username|password|role |
| `movies.txt` | movie data (pipe-separated) |
| `bookings.txt` | all booking records |
| `<MovieTitle>_seats.txt` | seat occupancy grid per movie |

---

## 🎨 UI Design

- Dark theme throughout (`#0d1117` background — GitHub Dark style)
- Responsive sidebar navigation
- Colour-coded seat picker
- Booking confirmation dialog with full receipt
