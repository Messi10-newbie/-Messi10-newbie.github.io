import java.io.*;
import java.util.ArrayList;
import java.util.HashMap;

public class SeatManager {

    private final int rows = 10;
    private final int cols = 10;

    private boolean[][] seats;
    private double[][]  prices;
    private String[][]  types;
    private String      currentMovieFile;

    private static final String BOOKING_FILE = "bookings.txt";

    public SeatManager() {
        seats  = new boolean[rows][cols];
        prices = new double[rows][cols];
        types  = new String[rows][cols];
    }

    // =============================================
    // Merged from BookingManager: Save booking
    // =============================================
    public static void saveBooking(String movieTitle, String seatName, String ticketType, double price) {
        try (FileWriter fw = new FileWriter(BOOKING_FILE, true)) {
            fw.write(movieTitle + "," + seatName + "," + ticketType + "," + price + "\n");
        } catch (IOException e) {
            System.err.println("Error saving booking: " + e.getMessage());
        }
    }

    // Merged from BookingManager: Load all bookings
    public static ArrayList<Booking> loadAllBookings() {
        ArrayList<Booking> bookings = new ArrayList<>();
        File file = new File(BOOKING_FILE);
        if (!file.exists()) return bookings;

        try (BufferedReader br = new BufferedReader(new FileReader(file))) {
            String line;
            while ((line = br.readLine()) != null) {
                String[] parts = line.split(",");
                if (parts.length == 4) {
                    bookings.add(new Booking(parts[0], parts[1], parts[2], Double.parseDouble(parts[3])));
                }
            }
        } catch (IOException e) {
            System.err.println("Error loading bookings: " + e.getMessage());
        } catch (NumberFormatException e) {
            System.err.println("Error parsing booking price: " + e.getMessage());
        }
        return bookings;
    }

    // Merged from BookingManager: Best Selling Movies report
    public static String getBestSellingMoviesReport() {
        ArrayList<Booking> bookings = loadAllBookings();
        HashMap<String, Integer> movieCount = new HashMap<>();

        for (Booking b : bookings) {
            String movie = b.getMovie();
            movieCount.put(movie, movieCount.getOrDefault(movie, 0) + 1);
        }

        if (movieCount.isEmpty()) return "No bookings found.";

        StringBuilder sb = new StringBuilder("=== Best Selling Movies ===\n");
        movieCount.entrySet().stream()
            .sorted((a, b) -> b.getValue() - a.getValue())
            .forEach(e -> sb.append(e.getKey()).append(": ").append(e.getValue()).append(" bookings\n"));
        sb.append("Total Revenue: RM").append(String.format("%.2f", getTotalRevenue()));
        return sb.toString();
    }

    // Merged from BookingManager: Total revenue
    public static double getTotalRevenue() {
        ArrayList<Booking> bookings = loadAllBookings();
        double total = 0.0;
        for (Booking b : bookings) total += b.getPrice();
        return total;
    }

    // =============================================
    // Seat file name helper
    // =============================================
    private String getSeatFileName(String movieTitle) {
        String baseName = movieTitle.replaceAll("_seats\\.txt$", "");
        baseName = baseName.replaceAll("[^a-zA-Z0-9]", "_");
        return baseName + "_seats.txt";
    }

    // =============================================
    // Load seats for a movie
    // =============================================
    public void loadMovie(String movieTitle) {
        currentMovieFile = getSeatFileName(movieTitle);

        for (int r = 0; r < rows; r++) {
            for (int c = 0; c < cols; c++) {
                seats[r][c]  = false;
                prices[r][c] = 0.0;
                types[r][c]  = "";
            }
        }

        File file = new File(currentMovieFile);
        if (!file.exists()) return;

        try (BufferedReader br = new BufferedReader(new FileReader(file))) {
            String line;
            while ((line = br.readLine()) != null) {
                String[] parts = line.split(",");
                if (parts.length == 4) {
                    int r = Integer.parseInt(parts[0]);
                    int c = Integer.parseInt(parts[1]);
                    seats[r][c]  = true;
                    types[r][c]  = parts[2];
                    prices[r][c] = Double.parseDouble(parts[3]);
                }
            }
        } catch (IOException e) {
            System.err.println("Error loading seats: " + e.getMessage());
        } catch (NumberFormatException e) {
            System.err.println("Error parsing seat data: " + e.getMessage());
        }
    }

    // =============================================
    // Book a seat
    // =============================================
    public boolean bookSeat(int row, int col, String type, double price) {
        if (!isValid(row, col)) return false;
        if (seats[row][col]) return false;

        seats[row][col]  = true;
        types[row][col]  = type;
        prices[row][col] = price;

        // Save to bookings.txt
        String seatName = "R" + (row + 1) + "C" + (col + 1);
        try (PrintWriter pw = new PrintWriter(new FileWriter(BOOKING_FILE, true))) {
            pw.println(currentMovieFile + "," + seatName + "," + type + "," + price);
        } catch (IOException e) {
            System.err.println("Error saving booking: " + e.getMessage());
        }

        saveAllSeats();
        return true;
    }

    // =============================================
    // Cancel / reset a seat
    // =============================================
    public void resetSeat(String movieTitle, String seatName) {
        currentMovieFile = getSeatFileName(movieTitle);
        loadMovie(movieTitle);

        try {
            int row = Integer.parseInt(seatName.substring(1, seatName.indexOf('C'))) - 1;
            int col = Integer.parseInt(seatName.substring(seatName.indexOf('C') + 1)) - 1;

            if (isValid(row, col)) {
                seats[row][col]  = false;
                types[row][col]  = "";
                prices[row][col] = 0.0;
                saveAllSeats();
            }
        } catch (NumberFormatException e) {
            System.err.println("Invalid seat name format: " + seatName);
        }
    }

    // =============================================
    // Save all seats to file
    // =============================================
    private void saveAllSeats() {
        if (currentMovieFile == null) return;

        boolean hasBooked = false;
        for (int i = 0; i < rows; i++)
            for (int j = 0; j < cols; j++)
                if (seats[i][j]) { hasBooked = true; break; }

        if (!hasBooked) {
            new File(currentMovieFile).delete();
            return;
        }

        try (PrintWriter pw = new PrintWriter(new FileWriter(currentMovieFile))) {
            for (int r = 0; r < rows; r++)
                for (int c = 0; c < cols; c++)
                    if (seats[r][c])
                        pw.println(r + "," + c + "," + types[r][c] + "," + prices[r][c]);
        } catch (IOException e) {
            System.err.println("Error saving seats: " + e.getMessage());
        }
    }

    // =============================================
    // Getters for UI
    // =============================================
    public boolean isBooked(int r, int c) { return isValid(r, c) && seats[r][c]; }
    public String  getType(int r, int c)  { return types[r][c]; }
    public double  getPrice(int r, int c) { return prices[r][c]; }
    public int     getRows()              { return rows; }
    public int     getCols()              { return cols; }

    private boolean isValid(int r, int c) {
        return r >= 0 && r < rows && c >= 0 && c < cols;
    }

    public int getBookedCount() {
        int count = 0;
        for (int i = 0; i < rows; i++)
            for (int j = 0; j < cols; j++)
                if (seats[i][j]) count++;
        return count;
    }

    public double getTotalPrice() {
        double total = 0.0;
        for (int i = 0; i < rows; i++)
            for (int j = 0; j < cols; j++)
                if (seats[i][j]) total += prices[i][j];
        return total;
    }
}
