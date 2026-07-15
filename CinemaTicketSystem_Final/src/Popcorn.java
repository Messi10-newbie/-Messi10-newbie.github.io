public class Popcorn extends ConcessionItem {

    public Popcorn(String size, int quantity) {
        super(size + " Popcorn", getPopcornPrice(size), quantity);
    }

    private static double getPopcornPrice(String size) {
        switch (size) {
            case "Small":  return 8.0;
            case "Medium": return 11.0;
            case "Large":  return 14.0;
            default:       return 8.0;
        }
    }

    @Override
    public String getCategory() { return "Popcorn"; }

    @Override
    public int hashCode() { return super.hashCode(); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Popcorn)) return false;
        return super.equals(obj);
    }
}
