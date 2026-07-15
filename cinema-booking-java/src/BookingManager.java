import java.io.*;
import java.util.*;
import java.util.stream.*;

public class BookingManager {

    private static final String FILE = "bookings.txt";

    public static List<Booking> loadAll() {
        List<Booking> list = new ArrayList<>();
        try (BufferedReader br = new BufferedReader(new FileReader(FILE))) {
            String line;
            while ((line = br.readLine()) != null) {
                if (!line.isBlank()) {
                    Booking b = Booking.fromFileString(line.trim());
                    if (b != null) list.add(b);
                }
            }
        } catch (IOException ignored) {}
        return list;
    }

    public static void saveAll(List<Booking> bookings) {
        try (PrintWriter pw = new PrintWriter(new FileWriter(FILE))) {
            for (Booking b : bookings) pw.println(b.toFileString());
        } catch (IOException e) { e.printStackTrace(); }
    }

    public static void addBooking(Booking b) {
        List<Booking> list = loadAll();
        list.add(b);
        saveAll(list);
    }

    public static List<Booking> getBookingsForUser(String username) {
        return loadAll().stream()
                .filter(b -> b.getUsername().equalsIgnoreCase(username))
                .collect(Collectors.toList());
    }

    public static List<Booking> getBookingsForMovie(String movieTitle) {
        return loadAll().stream()
                .filter(b -> b.getMovieTitle().equals(movieTitle))
                .collect(Collectors.toList());
    }

    public static boolean cancelBooking(String bookingId, String showtimeId, String seatCode) {
        List<Booking> list = loadAll();
        boolean found = false;
        for (Booking b : list) {
            if (b.getBookingId().equals(bookingId)) {
                b.setStatus("CANCELLED");
                found = true;
                break;
            }
        }
        if (found) {
            saveAll(list);
            if (!"N/A".equals(showtimeId)) {
                SeatManager.freeSeat(showtimeId, seatCode);
            }
        }
        return found;
    }

    public static long totalBookings() {
        return loadAll().stream().filter(b -> b.getStatus().equals("CONFIRMED")).count();
    }

    public static double totalRevenue() {
        return loadAll().stream()
                .filter(b -> b.getStatus().equals("CONFIRMED"))
                .mapToDouble(Booking::getTotalPrice)
                .sum();
    }

    public static Map<String, Long> bookingsByMovie() {
        return loadAll().stream()
                .filter(b -> b.getStatus().equals("CONFIRMED"))
                .collect(Collectors.groupingBy(Booking::getMovieTitle, Collectors.counting()));
    }
}
