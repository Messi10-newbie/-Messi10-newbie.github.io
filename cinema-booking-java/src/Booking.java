import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

public class Booking {
    private String bookingId;
    private String username;
    private String movieTitle;
    private String seatCode;
    private String seatType;
    private double totalPrice;
    private String snacks;
    private String bookingDate;
    private String status;
    private String showtimeId;
    private String showtimeDisplay;

    public Booking(String bookingId, String username, String movieTitle,
                   String seatCode, String seatType, double totalPrice,
                   String snacks, String bookingDate, String status,
                   String showtimeId, String showtimeDisplay) {
        this.bookingId       = bookingId;
        this.username        = username;
        this.movieTitle      = movieTitle;
        this.seatCode        = seatCode;
        this.seatType        = seatType;
        this.totalPrice      = totalPrice;
        this.snacks          = snacks;
        this.bookingDate     = bookingDate;
        this.status          = status;
        this.showtimeId      = showtimeId;
        this.showtimeDisplay = showtimeDisplay;
    }

    public static Booking create(String username, String movieTitle,
                                 String seatCode, String seatType,
                                 double totalPrice, String snacks,
                                 String showtimeId, String showtimeDisplay) {
        String id   = "BK" + System.currentTimeMillis();
        String date = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm"));
        return new Booking(id, username, movieTitle, seatCode, seatType,
                           totalPrice, snacks, date, "CONFIRMED",
                           showtimeId, showtimeDisplay);
    }

    public String getBookingId()       { return bookingId; }
    public String getUsername()        { return username; }
    public String getMovieTitle()      { return movieTitle; }
    public String getSeatCode()        { return seatCode; }
    public String getSeatType()        { return seatType; }
    public double getTotalPrice()      { return totalPrice; }
    public String getSnacks()          { return snacks; }
    public String getBookingDate()     { return bookingDate; }
    public String getStatus()          { return status; }
    public String getShowtimeId()      { return showtimeId; }
    public String getShowtimeDisplay() { return showtimeDisplay; }
    public void   setStatus(String s)  { this.status = s; }

    public String toFileString() {
        return bookingId + "|" + username + "|" + movieTitle + "|" +
               seatCode + "|" + seatType + "|" + totalPrice + "|" +
               snacks + "|" + bookingDate + "|" + status + "|" +
               showtimeId + "|" + showtimeDisplay;
    }

    public static Booking fromFileString(String line) {
        String[] p = line.split("\\|", 11);
        if (p.length < 9) return null;
        String stId  = p.length > 9  ? p[9]  : "N/A";
        String stDsp = p.length > 10 ? p[10] : "N/A";
        try {
            return new Booking(p[0], p[1], p[2], p[3], p[4],
                               Double.parseDouble(p[5]), p[6], p[7], p[8],
                               stId, stDsp);
        } catch (NumberFormatException e) { return null; }
    }
}
