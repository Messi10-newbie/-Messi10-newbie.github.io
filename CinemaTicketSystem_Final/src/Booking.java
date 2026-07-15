import java.util.Objects;

public class Booking {
    private String movie;
    private String seat;
    private String type;
    private double price;

    public Booking(String movie, String seat, String type, double price) {
        this.movie = movie;
        this.seat  = seat;
        this.type  = type;
        this.price = price;
    }

    // Getters
    public String getMovie() { return movie; }
    public String getSeat()  { return seat; }
    public String getType()  { return type; }
    public double getPrice() { return price; }

    // Setters
    public void setMovie(String movie) { this.movie = movie; }
    public void setSeat(String seat)   { this.seat = seat; }
    public void setType(String type)   { this.type = type; }
    public void setPrice(double price) { this.price = price; }

    // Merged from PriceCalculator.java
    public static double calculatePrice(String type) {
        switch (type) {
            case "Student": return 15.0;
            case "Adult":   return 20.0;
            case "VIP":     return 35.0;
            default:        return 20.0;
        }
    }

    @Override
    public String toString() {
        return movie + " | " + seat + " | " + type + " | RM" + String.format("%.2f", price);
    }

    @Override
    public int hashCode() { return Objects.hash(movie, seat); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Booking)) return false;
        Booking other = (Booking) obj;
        return movie.equals(other.movie) && seat.equals(other.seat);
    }
}
