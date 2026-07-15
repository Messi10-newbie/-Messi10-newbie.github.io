import java.io.*;
import java.util.*;

/**
 * Manages a 10×10 seat grid for each movie.
 * Rows A–J, Cols 1–10.
 * Seat types:
 *   Rows A-C  → VIP       (top)
 *   Rows D-G  → Standard  (middle)
 *   Rows H-J  → Economy   (back)
 *
 * File format: one row per line, 10 chars (0=available, 1=booked)
 */
public class SeatManager {

    public static final int ROWS = 10;
    public static final int COLS = 10;

    public static String getSeatType(int row) {
        if (row < 3) return "VIP";
        if (row < 7) return "Standard";
        return "Economy";
    }

    public static char rowLetter(int row) {
        return (char)('A' + row);
    }

    private static String fileName(String movieTitle) {
        String base = movieTitle.replaceAll("[^a-zA-Z0-9]", "_");
        return base + "_seats.txt";
    }

    /** Create empty seat file if it doesn't exist */
    public static void initSeats(String movieTitle) {
        File f = new File(fileName(movieTitle));
        if (!f.exists()) {
            boolean[][] seats = new boolean[ROWS][COLS];
            save(movieTitle, seats);
        }
    }

    public static boolean[][] load(String movieTitle) {
        boolean[][] seats = new boolean[ROWS][COLS];
        try (BufferedReader br = new BufferedReader(new FileReader(fileName(movieTitle)))) {
            for (int r = 0; r < ROWS; r++) {
                String line = br.readLine();
                if (line == null) break;
                for (int c = 0; c < COLS && c < line.length(); c++) {
                    seats[r][c] = line.charAt(c) == '1';
                }
            }
        } catch (IOException ignored) {
            // return all-false (all available)
        }
        return seats;
    }

    public static void save(String movieTitle, boolean[][] seats) {
        try (PrintWriter pw = new PrintWriter(new FileWriter(fileName(movieTitle)))) {
            for (int r = 0; r < ROWS; r++) {
                StringBuilder sb = new StringBuilder();
                for (int c = 0; c < COLS; c++) sb.append(seats[r][c] ? '1' : '0');
                pw.println(sb);
            }
        } catch (IOException e) { e.printStackTrace(); }
    }

    /** Books a seat. Returns false if already booked. */
    public static boolean bookSeat(String movieTitle, String seatCode) {
        int[] rc = parseCode(seatCode);
        if (rc == null) return false;
        boolean[][] seats = load(movieTitle);
        if (seats[rc[0]][rc[1]]) return false; // already booked
        seats[rc[0]][rc[1]] = true;
        save(movieTitle, seats);
        return true;
    }

    /** Frees a seat (for cancellation). */
    public static void freeSeat(String movieTitle, String seatCode) {
        int[] rc = parseCode(seatCode);
        if (rc == null) return;
        boolean[][] seats = load(movieTitle);
        seats[rc[0]][rc[1]] = false;
        save(movieTitle, seats);
    }

    /** Returns "A1", "B3", etc. */
    public static String toCode(int row, int col) {
        return "" + rowLetter(row) + (col + 1);
    }

    public static int[] parseCode(String code) {
        if (code == null || code.length() < 2) return null;
        int row = code.charAt(0) - 'A';
        int col;
        try { col = Integer.parseInt(code.substring(1)) - 1; } catch (NumberFormatException e) { return null; }
        if (row < 0 || row >= ROWS || col < 0 || col >= COLS) return null;
        return new int[]{row, col};
    }

    public static int availableCount(String movieTitle) {
        boolean[][] seats = load(movieTitle);
        int count = 0;
        for (boolean[] row : seats) for (boolean b : row) if (!b) count++;
        return count;
    }
}
