#define _CRT_SECURE_NO_WARNINGS
#include <iostream>
#include <iomanip>
#include <string>
#include <fstream>
#include <sstream>
#include <cstdio>
#include <cstring>
using namespace std;

const int MAX_BOOKINGS = 1000;
const int MAX_ROOMS = 200;
const string BOOKINGS_FILE = "bookings.txt";
const string ROOMS_FILE = "rooms.txt";

// ==================== DATE HELPERS (from User code) ====================
int daysInMonthGlobal[] = { 31,28,31,30,31,30,31,31,30,31,30,31 };

bool isLeapYearGlobal(int year) {
    return (year % 4 == 0 && year % 100 != 0) || (year % 400 == 0);
}

bool isValidDateGlobal(int day, int month, int year) {
    if (year < 2025) return false;
    if (month < 1 || month > 12) return false;
    if (day < 1) return false;

    int maxDay = daysInMonthGlobal[month - 1];
    if (month == 2 && isLeapYearGlobal(year)) maxDay = 29;
    if (day > maxDay) return false;

    return true;
}

int dateToDaysGlobal(int day, int month, int year) {
    int days = day;
    for (int i = 0; i < month - 1; i++) {
        days += daysInMonthGlobal[i];
        if (i == 1 && isLeapYearGlobal(year)) days++; // February is leap day womp womp
    }
    days += year * 365 + (year / 4 - year / 100 + year / 400);
    return days;
}

// Helper to parse DD/MM/YYYY -> day,month,year
bool parseDate(const string& s, int& day, int& month, int& year) {
    day = month = year = 0;
    int c = sscanf(s.c_str(), "%d/%d/%d", &day, &month, &year);
    if (c != 3) return false;
    return true;
}

// ==================== ROOM STRUCT & STORAGE (admin) ====================
struct Room {
    int roomNumber;        // unique
    string roomType;       // e.g., "Standard Room"
    double pricePerNight;
    bool available;
};

Room rooms[MAX_ROOMS];
int roomCount = 0;

// Load rooms from ROOMS_FILE (simple CSV): roomNumber|roomType|price|available
void loadRooms() {
    roomCount = 0;
    ifstream fin(ROOMS_FILE.c_str());
    if (!fin.is_open()) return;
    string line;
    while (getline(fin, line) && roomCount < MAX_ROOMS) {
        if (line.size() == 0) continue;
        stringstream ss(line);
        string token;
        Room r;
        // roomNumber
        if (!getline(ss, token, '|')) continue;
        r.roomNumber = atoi(token.c_str());
        // roomType
        if (!getline(ss, token, '|')) continue;
        r.roomType = token;
        // price
        if (!getline(ss, token, '|')) continue;
        r.pricePerNight = atof(token.c_str());
        // available
        if (!getline(ss, token, '|')) continue;
        r.available = (token == "1");
        rooms[roomCount++] = r;
    }
    fin.close();
}

void saveRooms() {
    ofstream fout(ROOMS_FILE.c_str());
    if (!fout.is_open()) return;
    for (int i = 0; i < roomCount; ++i) {
        fout << rooms[i].roomNumber << "|" << rooms[i].roomType << "|" << rooms[i].pricePerNight << "|" << (rooms[i].available ? "1" : "0") << "\n";
    }
    fout.close();
}

// Find room index by roomNumber, or -1
int findRoomIndexByNumber(int number) {
    for (int i = 0; i < roomCount; ++i) {
        if (rooms[i].roomNumber == number) return i;
    }
    return -1;
}

// Find room index by type (first match) - useful when customers choose by type string
int findRoomIndexByType(const string& type) {
    for (int i = 0; i < roomCount; ++i) {
        if (rooms[i].roomType == type) return i;
    }
    return -1;
}

// ==================== BOOKING STRUCT & STORAGE (unified) ====================
struct Booking {
    string bookingID;     // auto-generated B001...
    string customerID;    // optional
    string customerName;
    string phoneNumber;
    string checkInDate;
    string checkOutDate;
    string roomType;
    int roomNumber;       // which room number was booked (0 if none)
    int nights;
    double roomPrice;     // per night
    double subtotal;
    double tax;
    double serviceCharge;
    double total;
    string paymentMethod;
    double amountPaid;
};

Booking bookings[MAX_BOOKINGS];
int bookingCount = 0;
int nextBookingSerial = 1;

// Load bookings from file bookings.txt (CSV-like)
void loadBookings() {
    bookingCount = 0;
    nextBookingSerial = 1;
    ifstream fin(BOOKINGS_FILE.c_str());
    if (!fin.is_open()) return;
    string line;
    while (getline(fin, line) && bookingCount < MAX_BOOKINGS) {
        if (line.size() == 0) continue;
        stringstream ss(line);
        Booking b;
        string token;
        // bookingID
        if (!getline(ss, token, '|')) continue;
        b.bookingID = token;
        // customerID
        if (!getline(ss, token, '|')) continue;
        b.customerID = token;
        // customerName
        if (!getline(ss, token, '|')) continue;
        b.customerName = token;
        // phoneNumber
        if (!getline(ss, token, '|')) continue;
        b.phoneNumber = token;
        // checkInDate
        if (!getline(ss, token, '|')) continue;
        b.checkInDate = token;
        // checkOutDate
        if (!getline(ss, token, '|')) continue;
        b.checkOutDate = token;
        // roomType
        if (!getline(ss, token, '|')) continue;
        b.roomType = token;
        // roomNumber
        if (!getline(ss, token, '|')) continue;
        b.roomNumber = atoi(token.c_str());
        // nights
        if (!getline(ss, token, '|')) continue;
        b.nights = atoi(token.c_str());
        // roomPrice
        if (!getline(ss, token, '|')) continue;
        b.roomPrice = atof(token.c_str());
        // subtotal
        if (!getline(ss, token, '|')) continue;
        b.subtotal = atof(token.c_str());
        // tax
        if (!getline(ss, token, '|')) continue;
        b.tax = atof(token.c_str());
        // serviceCharge
        if (!getline(ss, token, '|')) continue;
        b.serviceCharge = atof(token.c_str());
        // total
        if (!getline(ss, token, '|')) continue;
        b.total = atof(token.c_str());
        // paymentMethod
        if (!getline(ss, token, '|')) continue;
        b.paymentMethod = token;
        // amountPaid
        if (!getline(ss, token, '|')) continue;
        b.amountPaid = atof(token.c_str());

        bookings[bookingCount++] = b;

        // update nextBookingSerial from bookingID if larger
        if (b.bookingID.size() > 1 && b.bookingID[0] == 'B') {
            int s = atoi(b.bookingID.substr(1).c_str());
            if (s >= nextBookingSerial) nextBookingSerial = s + 1;
        }
    }
    fin.close();
}

void saveBookings() {
    ofstream fout(BOOKINGS_FILE.c_str());
    if (!fout.is_open()) return;
    for (int i = 0; i < bookingCount; ++i) {
        Booking& b = bookings[i];
        fout << b.bookingID << "|"
            << b.customerID << "|"
            << b.customerName << "|"
            << b.phoneNumber << "|"
            << b.checkInDate << "|"
            << b.checkOutDate << "|"
            << b.roomType << "|"
            << b.roomNumber << "|"
            << b.nights << "|"
            << b.roomPrice << "|"
            << b.subtotal << "|"
            << b.tax << "|"
            << b.serviceCharge << "|"
            << b.total << "|"
            << b.paymentMethod << "|"
            << b.amountPaid << "\n";
    }
    fout.close();
}

// Generate booking ID like B001
string generateBookingID() {
    char buf[16];
    sprintf(buf, "B%03d", nextBookingSerial++);
    return string(buf);
}

// ==================== Payment Helpers (Deve) ====================
// We will reuse their logic but use the real room price from the selected room.

float calculateTotalPayment(float roomPrice, int days, const string& method, double& out_serviceCharge, double& out_tax) {
    float basePrice = roomPrice * days;

    // service charge and tax choices (we add them here)
    // example service charge = 2% of base price
    out_serviceCharge = basePrice * 0.02; // 2%
    out_tax = basePrice * 0.06; // 6% tax

    // method adjustments
    if (method == "Online") {
        basePrice *= 0.95f; // 5% discount
    }
    else if (method.find("Card") != string::npos) {
        basePrice *= 1.02f; // 2% surcharge
    }
    // final total before service/tax
    float totalBefore = basePrice;
    float grandTotal = totalBefore + (float)out_serviceCharge + (float)out_tax;
    return grandTotal;
}

string getPaymentMethodInteractive() {
    int choice;
    cout << "\nSelect Payment Method:\n";
    cout << "1. Cash\n2. Card\n3. Online\n";
    cout << "Choice: ";
    cin >> choice;
    switch (choice) {
    case 1: return "Cash";
    case 2: {
        string cardNo;
        cout << "Enter last 4 digits of card: ";
        cin >> cardNo;
        return "Card (" + cardNo + ")";
    }
    case 3: return "Online";
    default: return "Unknown";
    }
}

// ==================== Admin (Joachim) MENU functions (kept but adapted) ====================
bool adminLogin() {
    string username, password;

    cout << "\n========== ADMIN LOGIN ==========\n";

    do {
        cout << "Username: ";
        cin >> username;
        cout << "Password: ";
        cin >> password;

        if (username == "admin" && password == "admin123") {
            cout << "Login successful!\n";
            return true;
        }
        else {
            cout << "Invalid. Please try again.\n";
        }
    } while (true);
}

void adminManageRooms() {
    int roomChoice;
    do {
        cout << "\n========== MANAGE ROOMS ==========\n";
        cout << "1. Add Room\n";
        cout << "2. Remove Room\n";
        cout << "3. View Rooms\n";
        cout << "4. Back\n";
        cout << "Enter choice (1-4): ";
        cin >> roomChoice;
        cin.ignore();

        if (roomChoice == 1) {
            if (roomCount >= MAX_ROOMS) {
                cout << "Cannot add more rooms. Maximum limit reached.\n";
                continue;
            }
            Room r;
            cout << "\nEnter room number: ";
            cin >> r.roomNumber;
            cin.ignore();

            // check duplicate
            if (findRoomIndexByNumber(r.roomNumber) != -1) {
                cout << "Room already exists!\n";
                continue;
            }

            cout << "Enter room type: ";
            getline(cin, r.roomType);
            cout << "Enter price: RM";
            cin >> r.pricePerNight;
            cin.ignore();
            r.available = true;
            rooms[roomCount++] = r;
            cout << "Room added!\n";
            saveRooms();
        }
        else if (roomChoice == 2) {
            int number;
            cout << "\nEnter room number to remove: ";
            cin >> number;
            cin.ignore();

            bool found = false;
            for (int i = 0; i < roomCount; i++) {
                if (rooms[i].roomNumber == number) {
                    // check if any active booking uses this room number
                    bool used = false;
                    for (int j = 0; j < bookingCount; ++j) {
                        if (bookings[j].roomNumber == number) {
                            used = true;
                            break;
                        }
                    }
                    if (used) {
                        cout << "Cannot remove room. It has existing bookings.\n";
                        found = true;
                        break;
                    }
                    // remove by overwrite last
                    rooms[i] = rooms[roomCount - 1];
                    roomCount--;
                    cout << "Room removed!\n";
                    saveRooms();
                    found = true;
                    break;
                }
            }
            if (!found) cout << "Room not found!\n";
        }
        else if (roomChoice == 3) {
            cout << "\n========== ALL ROOMS ==========\n";
            if (roomCount == 0) {
                cout << "No rooms.\n";
            }
            else {
                for (int i = 0; i < roomCount; i++) {
                    cout << "Room " << rooms[i].roomNumber << " (" << rooms[i].roomType << ") - RM"
                        << fixed << setprecision(2) << rooms[i].pricePerNight << "/night - "
                        << (rooms[i].available ? "Available" : "Occupied") << endl;
                }
            }
        }
        else if (roomChoice == 4) {
            cout << "Returning...\n";
            break;
        }
        else {
            cout << "Invalid choice.\n";
        }
    } while (true);
}

void adminViewBookings() {
    cout << "\n========== ALL BOOKINGS ==========\n";
    cout << "ID | Name | Phone | Room# | RoomType | Check-in | Check-out | Nights | Total\n";
    cout << "--------------------------------------------------------------------------\n";

    if (bookingCount == 0) {
        cout << "No bookings.\n";
    }
    else {
        for (int i = 0; i < bookingCount; ++i) {
            Booking& b = bookings[i];
            cout << b.bookingID << " | " << b.customerName << " | " << b.phoneNumber
                << " | " << b.roomNumber << " | " << b.roomType << " | "
                << b.checkInDate << " | " << b.checkOutDate << " | "
                << b.nights << " | RM" << fixed << setprecision(2) << b.total << endl;
        }
    }
}

void adminReportsMenu() {
    int choice;
    do {
        cout << "\n========== REPORTS (Admin Only) ==========\n";
        cout << "1. All Booking Records\n";
        cout << "2. Daily Report (by day & month)\n";
        cout << "3. Monthly Report (by month)\n";
        cout << "4. Back\n";
        cout << "Enter choice: ";
        cin >> choice;
        cin.ignore();

        if (choice == 1) {
            // Full detailed report
            cout << "\n=== All Booking Records ===\n\n";
            int totalBookings = 0;
            double totalSales = 0.0;
            for (int i = 0; i < bookingCount; ++i) {
                Booking& b = bookings[i];
                cout << "-------------------------------------------\n";
                cout << "Booking ID : " << b.bookingID << "\n";
                cout << "Customer   : " << b.customerName << " (" << b.phoneNumber << ")\n";
                cout << "Room       : " << b.roomType << " (Room#" << b.roomNumber << ")\n";
                cout << "Check-In   : " << b.checkInDate << "\n";
                cout << "Check-Out  : " << b.checkOutDate << "\n";
                cout << "Nights     : " << b.nights << "\n";
                cout << "Subtotal   : RM " << fixed << setprecision(2) << b.subtotal << "\n";
                cout << "Service    : RM " << b.serviceCharge << "\n";
                cout << "Tax        : RM " << b.tax << "\n";
                cout << "TOTAL      : RM " << b.total << "\n";
                cout << "Paid via   : " << b.paymentMethod << " (Paid: RM " << b.amountPaid << ")\n";
                cout << "-------------------------------------------\n";
                totalBookings++;
                totalSales += b.total;
            }
            cout << "Total Bookings: " << totalBookings << "\n";
            cout << "Total Sales: RM " << fixed << setprecision(2) << totalSales << "\n\n";
        }
        else if (choice == 2) {
            int d, m;
            cout << "Enter day (1-31): ";
            cin >> d;
            cout << "Enter month (1-12): ";
            cin >> m;
            cout << "\n=== Daily Report (" << d << "/" << m << ") ===\n\n";
            int totalBookings = 0;
            double totalSales = 0.0;
            for (int i = 0; i < bookingCount; ++i) {
                Booking& b = bookings[i];
                int bd, bm, by;
                if (!parseDate(b.checkInDate, bd, bm, by)) continue;
                if (bd == d && bm == m) {
                    cout << b.bookingID << " | " << b.customerName << " | " << b.roomType << " | RM " << b.total << "\n";
                    totalBookings++;
                    totalSales += b.total;
                }
            }
            cout << "Total Bookings: " << totalBookings << "\n";
            cout << "Total Sales: RM " << fixed << setprecision(2) << totalSales << "\n\n";
        }
        else if (choice == 3) {
            int m;
            cout << "Enter month (1-12): ";
            cin >> m;
            cout << "\n=== Monthly Report (Month " << m << ") ===\n\n";
            int totalBookings = 0;
            double totalSales = 0.0;
            for (int i = 0; i < bookingCount; ++i) {
                Booking& b = bookings[i];
                int bd, bm, by;
                if (!parseDate(b.checkInDate, bd, bm, by)) continue;
                if (bm == m) {
                    cout << b.bookingID << " | " << b.customerName << " | " << b.roomType << " | RM " << b.total << "\n";
                    totalBookings++;
                    totalSales += b.total;
                }
            }
            cout << "Total Bookings: " << totalBookings << "\n";
            cout << "Total Sales: RM " << fixed << setprecision(2) << totalSales << "\n\n";
        }
        else if (choice == 4) {
            break;
        }
        else {
            cout << "Invalid choice.\n";
        }
    } while (true);
}

void adminMenu() {
    if (!adminLogin()) return;
    int choice;
    do {
        cout << "\n========== ADMIN MENU ==========\n";
        cout << "1. Manage Rooms\n";
        cout << "2. View Bookings\n";
        cout << "3. Reports\n";
        cout << "4. Logout\n";
        cout << "Enter choice (1-4): ";
        cin >> choice;
        cin.ignore();
        if (choice == 1) adminManageRooms();
        else if (choice == 2) adminViewBookings();
        else if (choice == 3) adminReportsMenu();
        else if (choice == 4) { cout << "Logging out...\n"; break; }
        else cout << "Invalid choice.\n";
    } while (true);
}

// ==================== Customer (Booking & Invoice & Payment & Modify) ====================

// Show available rooms (pulled from admin-managed list)
void showRoomMenuFromAdmin() {
    cout << "\n===== ROOM TYPES (From Admin) =====\n";
    if (roomCount == 0) {
        cout << "No rooms defined by admin. Please ask admin to add rooms.\n";
        return;
    }
    for (int i = 0; i < roomCount; ++i) {
        cout << (i + 1) << ". Room#" << rooms[i].roomNumber << " - " << rooms[i].roomType
            << " - RM " << fixed << setprecision(2) << rooms[i].pricePerNight << " per night\n";
    }
}

// Customer makes booking (uses admin rooms)
Booking makeBookingInteractive() {
    Booking b;
    b.bookingID = generateBookingID();

    cout << "\n====== MAKE A BOOKING ======\n";
    cout << "Enter customer name: ";
    getline(cin, b.customerName);
    cout << "Enter phone number: ";
    getline(cin, b.phoneNumber);

    // choose room from admin list
    if (roomCount == 0) {
        cout << "No rooms available (admin hasn't added any). Booking aborted.\n";
        b.bookingID = ""; // mark invalid
        return b;
    }
    showRoomMenuFromAdmin();
    int choiceIdx;
    while (true) {
        cout << "Choose a room by number (1-" << roomCount << "): ";
        if (!(cin >> choiceIdx)) {
            cout << "Invalid input.\n";
            cin.clear();
            while (cin.get() != '\n');
            continue;
        }
        if (choiceIdx < 1 || choiceIdx > roomCount) {
            cout << "Invalid choice.\n";
            continue;
        }
        break;
    }
    cin.ignore();

    Room chosen = rooms[choiceIdx - 1];
    b.roomType = chosen.roomType;
    b.roomNumber = chosen.roomNumber;
    b.roomPrice = chosen.pricePerNight;

    // date input with validation
    int day, month, year;
    while (true) {
        cout << "Enter check-in date (DD/MM/YYYY): ";
        getline(cin, b.checkInDate);
        if (!parseDate(b.checkInDate, day, month, year) || !isValidDateGlobal(day, month, year)) {
            cout << "Invalid date! Try again.\n";
            continue;
        }
        break;
    }
    int d1 = day, m1 = month, y1 = year;

    while (true) {
        cout << "Enter check-out date (DD/MM/YYYY): ";
        getline(cin, b.checkOutDate);
        if (!parseDate(b.checkOutDate, day, month, year) || !isValidDateGlobal(day, month, year)) {
            cout << "Invalid date! Try again.\n";
            continue;
        }
        break;
    }
    int d2 = day, m2 = month, y2 = year;

    b.nights = dateToDaysGlobal(d2, m2, y2) - dateToDaysGlobal(d1, m1, y1);
    if (b.nights <= 0) {
        cout << "Error: Check-out must be after check-in! Setting nights = 1.\n";
        b.nights = 1;
    }

    b.subtotal = b.roomPrice * b.nights;

    // default payment placeholders
    b.tax = 0.0;
    b.serviceCharge = 0.0;
    b.total = b.subtotal;
    b.paymentMethod = "NotPaid";
    b.amountPaid = 0.0;

    cout << "\nBooking recorded for " << b.customerName
        << " (" << b.phoneNumber << ") from "
        << b.checkInDate << " to " << b.checkOutDate
        << " in Room#" << b.roomNumber << " (" << b.roomType << ")"
        << " for " << b.nights << " nights.\n";

    // save to array
    if (b.bookingID != "") {
        bookings[bookingCount++] = b;
        saveBookings();
    }
    return b;
}

// Print invoice for a booking (Cheng style + unified values)
void printInvoiceUnified(const Booking& b) {
    cout << string(50, '=') << endl;
    cout << setw(30) << "INVOICE" << endl;
    cout << string(50, '=') << endl;
    cout << "Booking ID   : " << b.bookingID << endl;
    cout << "Customer Name: " << b.customerName << endl;
    cout << "Phone Number : " << b.phoneNumber << endl;
    cout << "CheckIn Date : " << b.checkInDate << endl;
    cout << "CheckOut Date: " << b.checkOutDate << endl;
    cout << "Room Type    : " << b.roomType << " (Room#" << b.roomNumber << ")" << endl;
    cout << "Nights       : " << b.nights << endl;
    cout << "Room Price   : RM " << fixed << setprecision(2) << b.roomPrice << endl;
    cout << string(50, '-') << endl;

    cout << "Subtotal: RM " << fixed << setprecision(2) << b.subtotal << endl;
    cout << "Service : RM " << fixed << setprecision(2) << b.serviceCharge << endl;
    cout << "Tax     : RM " << fixed << setprecision(2) << b.tax << endl;
    cout << "TOTAL   : RM " << fixed << setprecision(2) << b.total << endl;
    cout << string(50, '=') << endl;
}

// Print receipt (user's receipt format + paid info)
void printReceiptUnified(const Booking& b) {
    cout << "\n================= HOTEL RECEIPT =================\n";
    cout << "Customer   : " << b.customerName << endl;
    cout << "Phone      : " << b.phoneNumber << endl;
    cout << "Check-in   : " << b.checkInDate << endl;
    cout << "Check-out  : " << b.checkOutDate << endl;
    cout << "Room Type  : " << b.roomType << " (Room#" << b.roomNumber << ")\n";
    cout << "Nights     : " << b.nights << endl;
    cout << "-----------------------------------------------\n";
    cout << fixed << setprecision(2);
    cout << "Subtotal       : RM " << b.subtotal << endl;
    cout << "Service Charge : RM " << b.serviceCharge << endl;
    cout << "Tax            : RM " << b.tax << endl;
    cout << "-----------------------------------------------\n";
    cout << "TOTAL          : RM " << b.total << endl;
    cout << "-----------------------------------------------\n";
    cout << "Payment Method : " << b.paymentMethod << endl;
    cout << "Amount Paid    : RM " << b.amountPaid << endl;
    cout << "Change         : RM " << (b.amountPaid - b.total) << endl;
    cout << "=================================================\n";
    cout << "   Thank you for booking with us! Have a nice day.\n";
    cout << "=================================================\n";
}

// Find booking index by bookingID
int findBookingIndexByID(const string& id) {
    for (int i = 0; i < bookingCount; ++i) {
        if (bookings[i].bookingID == id) return i;
    }
    return -1;
}

// Customer modifies booking (expanded: can update name/phone/dates/room/payment)
void modifyBookingInteractive() {
    string id;
    cout << "Enter Booking ID to modify (e.g., B001): ";
    getline(cin, id);
    int idx = findBookingIndexByID(id);
    if (idx == -1) {
        cout << "Booking not found.\n";
        return;
    }
    Booking& b = bookings[idx];
    cout << "Modifying booking " << b.bookingID << " for " << b.customerName << "\n";

    cout << "Enter new customer name (leave empty to keep): ";
    string tmp;
    getline(cin, tmp);
    if (tmp.size() > 0) b.customerName = tmp;

    cout << "Enter new phone number (leave empty to keep): ";
    getline(cin, tmp);
    if (tmp.size() > 0) b.phoneNumber = tmp;

    cout << "Change room? (y/n): ";
    getline(cin, tmp);
    if (tmp == "y" || tmp == "Y") {
        if (roomCount == 0) {
            cout << "No rooms available in system.\n";
        }
        else {
            showRoomMenuFromAdmin();
            int c;
            while (true) {
                cout << "Choose a room by number (1-" << roomCount << "): ";
                if (!(cin >> c)) {
                    cout << "Invalid input.\n";
                    cin.clear();
                    while (cin.get() != '\n');
                    continue;
                }
                if (c < 1 || c > roomCount) {
                    cout << "Invalid choice.\n";
                    continue;
                }
                break;
            }
            cin.ignore();
            Room chosen = rooms[c - 1];
            b.roomType = chosen.roomType;
            b.roomNumber = chosen.roomNumber;
            b.roomPrice = chosen.pricePerNight;
        }
    }

    cout << "Change dates? (y/n): ";
    getline(cin, tmp);
    int day, month, year;
    if (tmp == "y" || tmp == "Y") {
        while (true) {
            cout << "Enter check-in date (DD/MM/YYYY): ";
            getline(cin, b.checkInDate);
            if (!parseDate(b.checkInDate, day, month, year) || !isValidDateGlobal(day, month, year)) {
                cout << "Invalid date! Try again.\n";
                continue;
            }
            break;
        }
        int d1 = day, m1 = month, y1 = year;
        while (true) {
            cout << "Enter check-out date (DD/MM/YYYY): ";
            getline(cin, b.checkOutDate);
            if (!parseDate(b.checkOutDate, day, month, year) || !isValidDateGlobal(day, month, year)) {
                cout << "Invalid date! Try again.\n";
                continue;
            }
            break;
        }
        int d2 = day, m2 = month, y2 = year;
        b.nights = dateToDaysGlobal(d2, m2, y2) - dateToDaysGlobal(d1, m1, y1);
        if (b.nights <= 0) {
            cout << "Error: Check-out must be after check-in! Setting nights = 1.\n";
            b.nights = 1;
        }
    }

    // Recalculate subtotal/totals after any change
    b.subtotal = b.roomPrice * b.nights;

    cout << "Change payment? (y/n): ";
    getline(cin, tmp);
    if (tmp == "y" || tmp == "Y") {
        string method = getPaymentMethodInteractive();
        double svc = 0.0, tax = 0.0;
        double total = calculateTotalPayment((float)b.roomPrice, b.nights, method, svc, tax);
        b.paymentMethod = method;
        b.serviceCharge = svc;
        b.tax = tax;
        b.total = total;
        cout << "Enter amount paid: RM ";
        double paid;
        cin >> paid;
        cin.ignore();
        b.amountPaid = paid;
    }

    // save changes
    saveBookings();
    cout << "Booking updated successfully.\n";
    printInvoiceUnified(b);
    printReceiptUnified(b);
}

// Customer flow: booking -> invoice -> payment
void customerMenu() {
    int choice;
    do {
        cout << "\n=== CUSTOMER MENU ===\n";
        cout << "1. Make Booking\n";
        cout << "2. View/Print Invoice (by Booking ID)\n";
        cout << "3. Make Payment for Booking (by Booking ID)\n";
        cout << "4. Modify Booking\n";
        cout << "5. Back to Main Menu\n";
        cout << "Enter choice: ";
        if (!(cin >> choice)) {
            cout << "Invalid input. Try again.\n";
            cin.clear();
            while (cin.get() != '\n');
            continue;
        }
        cin.ignore();

        if (choice == 1) {
            Booking b = makeBookingInteractive();
            if (b.bookingID != "") {
                cout << "Booking created with ID: " << b.bookingID << "\n";
            }
        }
        else if (choice == 2) {
            string id;
            cout << "Enter Booking ID: ";
            getline(cin, id);
            int idx = findBookingIndexByID(id);
            if (idx == -1) {
                cout << "Booking not found.\n";
            }
            else {
                printInvoiceUnified(bookings[idx]);
            }
        }
        else if (choice == 3) {
            string id;
            cout << "Enter Booking ID for payment: ";
            getline(cin, id);
            int idx = findBookingIndexByID(id);
            if (idx == -1) {
                cout << "Booking not found.\n";
            }
            else {
                Booking& b = bookings[idx];
                if (b.nights <= 0) {
                    cout << "Booking has invalid nights. Cannot process payment.\n";
                    continue;
                }
                cout << "Booking found: " << b.bookingID << " | " << b.customerName << " | RM " << b.total << "\n";
                string method = getPaymentMethodInteractive();
                double svc = 0.0, tax = 0.0;
                double total = calculateTotalPayment((float)b.roomPrice, b.nights, method, svc, tax);
                b.paymentMethod = method;
                b.serviceCharge = svc;
                b.tax = tax;
                b.total = total;
                cout << "Total to pay (including service & tax): RM " << fixed << setprecision(2) << b.total << "\n";
                cout << "Enter amount paid: RM ";
                double paid;
                cin >> paid;
                cin.ignore();
                b.amountPaid = paid;
                // save booking
                saveBookings();
                printReceiptUnified(b);
            }
        }
        else if (choice == 4) {
            modifyBookingInteractive();
        }
        else if (choice == 5) {
            break;
        }
        else {
            cout << "Invalid choice.\n";
        }
    } while (true);
}

// ==================== ENTRY POINT: MAIN MENU ====================
int main() {
    // initial load
    loadRooms();
    loadBookings();

    // If there are no initial rooms, create default ones (so customers can book immediately)
    if (roomCount == 0) {
        Room r1 = { 101, "Standard Room", 150.0, true };
        Room r2 = { 102, "Deluxe Room", 250.0, true };
        Room r3 = { 201, "Suite", 400.0, true };
        rooms[roomCount++] = r1;
        rooms[roomCount++] = r2;
        rooms[roomCount++] = r3;
        saveRooms();
    }

    int choice;
    do {
        cout << "\n=== HOTEL BOOKING SYSTEM ===\n";
        cout << "1. Customer Menu (Booking / Invoice / Payment / Modify)\n";
        cout << "2. Admin Menu (Rooms / Bookings / Reports)\n";
        cout << "3. Exit\n";
        cout << "Choose (1-3): ";
        if (!(cin >> choice)) {
            cout << "Invalid input. Try again.\n";
            cin.clear();
            while (cin.get() != '\n');
            continue;
        }
        cin.ignore();

        if (choice == 1) customerMenu();
        else if (choice == 2) adminMenu();
        else if (choice == 3) {
            cout << "Saving data and exiting...\n";
            saveBookings();
            saveRooms();
            break;
        }
        else cout << "Invalid choice.\n";

    } while (true);

    return 0;
}
